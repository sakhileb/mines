<?php

namespace App\Livewire;

use App\Models\MineArea;
use App\Models\MinePlan;
use Livewire\Attributes\Validate;
use Livewire\Component;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class MinePlanUploader extends Component
{
    use WithFileUploads;

    public MineArea $mineArea;

    // Upload data
    #[Validate('required|file|mimes:pdf,dwg,dxf,png,jpg,jpeg|max:102400')] // 100MB max
    public $file = null;

    #[Validate('required|string|max:255')]
    public string $title = '';

    #[Validate('nullable|string|max:1000')]
    public ?string $description = '';

    // Georeferencing
    #[Validate('nullable|numeric|min:-90|max:90')]
    public ?float $refPointLat = null;

    #[Validate('nullable|numeric|min:-180|max:180')]
    public ?float $refPointLon = null;

    #[Validate('nullable|numeric|min:0.1')]
    public ?float $scale = 1.0;

    #[Validate('nullable|numeric|min:0|max:360')]
    public ?float $rotation = 0.0;

    // UI state
    public bool $showUploadForm = false;
    public bool $showEditForm = false;
    public ?MinePlan $editingPlan = null;
    public string $previewMode = 'list'; // list, upload, edit, preview
    public array $uploadProgress = [];
    public ?string $uploadError = null;
    public bool $isUploading = false;

    // Preview
    public ?MinePlan $previewPlan = null;
    public string $previewUrl = '';

    public function mount(MineArea $mineArea)
    {
        $this->mineArea = $mineArea;
        $this->authorize('view', $mineArea);
    }

    public function render()
    {
        return view('livewire.mine-plan-uploader.index', [
            'plans' => $this->mineArea->plans()->with('uploader')->latest()->paginate(10),
            'currentPlan' => $this->mineArea->plans()->where('is_current', true)->first(),
        ]);
    }

    /**
     * Show upload form.
     */
    public function showUploadForm()
    {
        $this->authorize('update', $this->mineArea);
        
        $this->reset(['file', 'title', 'description', 'refPointLat', 'refPointLon', 'scale', 'rotation', 'uploadError']);
        $this->previewMode = 'upload';
        $this->showUploadForm = true;
    }

    /**
     * Cancel upload.
     */
    public function cancelUpload()
    {
        $this->reset(['file', 'title', 'description', 'refPointLat', 'refPointLon', 'scale', 'rotation', 'uploadError', 'showUploadForm']);
        $this->previewMode = 'list';
    }

    /**
     * Handle file upload.
     */
    public function upload()
    {
        $this->authorize('update', $this->mineArea);
        
        $this->validate();
        $this->isUploading = true;
        $this->uploadError = null;

        try {
            // Get file type
            $ext = strtolower($this->file->getClientOriginalExtension());
            $fileType = match($ext) {
                'pdf' => 'pdf',
                'dwg' => 'dwg',
                'dxf' => 'dxf',
                'jpg', 'jpeg' => 'jpg',
                'png' => 'png',
                default => null,
            };

            if (!$fileType) {
                throw new \Exception('Unsupported file type');
            }

            // Store file
            $fileName = $this->file->getClientOriginalName();
            $fileSize = $this->file->getSize();
            $storagePath = "mine-areas/{$this->mineArea->id}/plans";
            
            $filePath = $this->file->storeAs(
                $storagePath,
                Str::uuid() . '.' . $ext,
                'private'
            );

            // Get version number
            $lastVersion = $this->mineArea->plans()->max('version') ?? 0;
            $version = $lastVersion + 1;

            // Check if we should make this current
            $isCurrent = $this->mineArea->plans()->count() === 0;

            // Create MinePlan record
            $plan = MinePlan::create([
                'mine_area_id' => $this->mineArea->id,
                'uploaded_by' => Auth::id(),
                'file_name' => $fileName,
                'file_path' => $filePath,
                'file_size' => $fileSize,
                'file_type' => $fileType,
                'title' => $this->title,
                'description' => $this->description,
                'version' => $version,
                'is_current' => $isCurrent,
                'scale' => $this->scale ?? 1.0,
                'reference_point_lat' => $this->refPointLat,
                'reference_point_lon' => $this->refPointLon,
                'rotation_degrees' => $this->rotation ?? 0.0,
                'status' => 'active',
                'metadata' => [
                    'original_filename' => $fileName,
                    'uploaded_at' => now(),
                    'uploaded_by_user' => Auth::user()->name,
                ],
            ]);

            // If this is the first plan or explicitly marked as current, update others
            if ($isCurrent) {
                $this->mineArea->plans()
                    ->where('id', '!=', $plan->id)
                    ->update(['is_current' => false]);
            }

            Log::info('Mine plan uploaded', [
                'plan_id' => $plan->id,
                'mine_area_id' => $this->mineArea->id,
                'user_id' => Auth::id(),
                'file_type' => $fileType,
            ]);

            $this->dispatch('notify',
                type: 'success',
                message: "Plan '{$fileName}' uploaded successfully!"
            );

            $this->cancelUpload();
            $this->dispatch('plan-uploaded', planId: $plan->id);

        } catch (\Exception $e) {
            Log::error('Failed to upload mine plan', [
                'mine_area_id' => $this->mineArea->id,
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
            ]);

            $this->uploadError = $e->getMessage();
            $this->dispatch('notify',
                type: 'error',
                message: 'Failed to upload plan: ' . $e->getMessage()
            );
        } finally {
            $this->isUploading = false;
        }
    }

    /**
     * Preview a plan.
     */
    public function previewPlan(MinePlan $plan)
    {
        $this->previewPlan = $plan;
        $this->previewMode = 'preview';
        $this->previewUrl = $this->generatePreviewUrl($plan);
    }

    /**
     * Generate preview URL based on file type.
     */
    private function generatePreviewUrl(MinePlan $plan): string
    {
        // For images, return storage URL
        if (in_array($plan->file_type, ['jpg', 'png'])) {
            return route('mine-plans.preview', $plan->id);
        }

        // For PDFs, would need a PDF viewer
        if ($plan->file_type === 'pdf') {
            return route('mine-plans.preview', $plan->id);
        }

        // For CAD files, would need special handling
        return '';
    }

    /**
     * Set as current plan.
     */
    public function setAsCurrent(MinePlan $plan)
    {
        $this->authorize('update', $this->mineArea);
        
        // Verify plan belongs to this mine area
        if ($plan->mine_area_id !== $this->mineArea->id) {
            abort(403, 'Unauthorized');
        }
        
        try {
            // Update all plans for this mine area
            $this->mineArea->plans()->update(['is_current' => false]);
            $plan->update(['is_current' => true]);

            $plan->markAsCurrent();

            Log::info('Mine plan set as current', [
                'plan_id' => $plan->id,
                'mine_area_id' => $this->mineArea->id,
                'user_id' => Auth::id(),
            ]);

            $this->dispatch('notify',
                type: 'success',
                message: "'{$plan->file_name}' is now the current plan"
            );

        } catch (\Exception $e) {
            $this->dispatch('notify',
                type: 'error',
                message: 'Failed to set as current plan'
            );
        }
    }

    /**
     * Start editing a plan.
     */
    public function startEditPlan(MinePlan $plan)
    {
        $this->authorize('update', $this->mineArea);
        
        // Verify plan belongs to this mine area
        if ($plan->mine_area_id !== $this->mineArea->id) {
            abort(403, 'Unauthorized');
        }
        
        $this->editingPlan = $plan;
        $this->title = $plan->title;
        $this->description = $plan->description;
        $this->refPointLat = $plan->reference_point_lat;
        $this->refPointLon = $plan->reference_point_lon;
        $this->scale = $plan->scale;
        $this->rotation = $plan->rotation_degrees;
        $this->previewMode = 'edit';
        $this->showEditForm = true;
    }

    /**
     * Cancel editing.
     */
    public function cancelEdit()
    {
        $this->reset(['editingPlan', 'title', 'description', 'refPointLat', 'refPointLon', 'scale', 'rotation', 'showEditForm']);
        $this->previewMode = 'list';
    }

    /**
     * Update plan metadata.
     */
    public function updatePlanMetadata()
    {
        $this->authorize('update', $this->mineArea);
        
        if (!$this->editingPlan) {
            return;
        }
        
        // Verify plan belongs to this mine area
        if ($this->editingPlan->mine_area_id !== $this->mineArea->id) {
            abort(403, 'Unauthorized');
        }

        $this->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'refPointLat' => 'nullable|numeric|min:-90|max:90',
            'refPointLon' => 'nullable|numeric|min:-180|max:180',
            'scale' => 'nullable|numeric|min:0.1',
            'rotation' => 'nullable|numeric|min:0|max:360',
        ]);

        try {
            $this->editingPlan->update([
                'title' => $this->title,
                'description' => $this->description,
                'reference_point_lat' => $this->refPointLat,
                'reference_point_lon' => $this->refPointLon,
                'scale' => $this->scale,
                'rotation_degrees' => $this->rotation,
            ]);

            Log::info('Mine plan metadata updated', [
                'plan_id' => $this->editingPlan->id,
                'user_id' => Auth::id(),
            ]);

            $this->dispatch('notify',
                type: 'success',
                message: 'Plan metadata updated successfully'
            );

            $this->cancelEdit();

        } catch (\Exception $e) {
            $this->dispatch('notify',
                type: 'error',
                message: 'Failed to update plan metadata'
            );
        }
    }

    /**
     * Archive a plan.
     */
    public function archivePlan(MinePlan $plan)
    {
        $this->authorize('update', $this->mineArea);
        
        // Verify plan belongs to this mine area
        if ($plan->mine_area_id !== $this->mineArea->id) {
            abort(403, 'Unauthorized');
        }
        
        try {
            $plan->update(['status' => 'archived']);

            // If archived plan was current, set next newest as current
            if ($plan->is_current) {
                $newCurrent = $this->mineArea->plans()
                    ->where('status', 'active')
                    ->latest('version')
                    ->first();

                if ($newCurrent) {
                    $newCurrent->update(['is_current' => true]);
                }
            }

            Log::info('Mine plan archived', [
                'plan_id' => $plan->id,
                'user_id' => Auth::id(),
            ]);

            $this->dispatch('notify',
                type: 'success',
                message: 'Plan archived successfully'
            );

        } catch (\Exception $e) {
            $this->dispatch('notify',
                type: 'error',
                message: 'Failed to archive plan'
            );
        }
    }

    /**
     * Delete a plan.
     */
    public function deletePlan(MinePlan $plan)
    {
        $this->authorize('delete', $this->mineArea);
        
        // Verify plan belongs to this mine area
        if ($plan->mine_area_id !== $this->mineArea->id) {
            abort(403, 'Unauthorized');
        }
        
        try {
            // Delete file from storage
            if (\Illuminate\Support\Facades\Storage::disk('private')->exists($plan->file_path)) {
                \Illuminate\Support\Facades\Storage::disk('private')->delete($plan->file_path);
            }

            $plan->delete();

            Log::info('Mine plan deleted', [
                'plan_id' => $plan->id,
                'user_id' => Auth::id(),
            ]);

            $this->dispatch('notify',
                type: 'success',
                message: 'Plan deleted successfully'
            );

        } catch (\Exception $e) {
            Log::error('Failed to delete mine plan', [
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
            ]);

            $this->dispatch('notify',
                type: 'error',
                message: 'Failed to delete plan'
            );
        }
    }

    /**
     * Download a plan.
     */
    public function downloadPlan(MinePlan $plan)
    {
        try {
            if (\Illuminate\Support\Facades\Storage::disk('private')->exists($plan->file_path)) {
                return \Illuminate\Support\Facades\Storage::disk('private')
                    ->download($plan->file_path, $plan->file_name);
            }

            $this->dispatch('notify',
                type: 'error',
                message: 'File not found'
            );

        } catch (\Exception $e) {
            $this->dispatch('notify',
                type: 'error',
                message: 'Failed to download plan'
            );
        }
    }

    /**
     * Get supported file types as human-readable string.
     */
    public function getSupportedFormats(): string
    {
        return 'PDF, DWG (AutoCAD), DXF, PNG, JPG';
    }

    /**
     * Format file size for display.
     */
    public function formatFileSize(int $bytes): string
    {
        if ($bytes < 1024) {
            return $bytes . ' B';
        } elseif ($bytes < 1024 * 1024) {
            return number_format($bytes / 1024, 2) . ' KB';
        } else {
            return number_format($bytes / 1024 / 1024, 2) . ' MB';
        }
    }
}
