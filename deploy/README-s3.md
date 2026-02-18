S3 configuration & verification

1) Set AWS environment variables in your host/staging environment (do NOT commit these to git):

-- AWS_ACCESS_KEY_ID (set via your secrets manager or CI secrets)
-- AWS_SECRET_ACCESS_KEY (set via your secrets manager or CI secrets)
- AWS_DEFAULT_REGION=your-region (e.g. us-east-1)
- AWS_BUCKET=your-bucket-name
- AWS_URL=optional (for custom endpoints)
- AWS_ENDPOINT=optional (for S3-compatible endpoints)

2) Configure `FILESYSTEM_DISK=s3` in production `.env` and ensure `FILESYSTEMS_DISK` default is correct in `config/filesystems.php`.

3) Ensure the S3 bucket is private. Use IAM permissions that are least-privilege (putObject/getObject/listObject for the specific bucket only).

4) Verify connectivity from the server:

- Install dependencies (if needed) and run the artisan storage check:

```bash
php artisan storage:verify-s3 --disk=s3
```

This command will attempt to upload a small test file, try to generate a temporary URL (if supported), and then delete the test file.

5) Test a real upload via the UI (Mine Area → Upload Mine Plan) and confirm the file appears in the S3 bucket. Then verify the application can generate a signed download URL (reports use signed URLs by default).

6) If the signed URL does not work, ensure the server time is correct (NTP) and that the IAM policy allows `s3:GetObject` for temporary URLs.

7) Monitoring & Security:
- Rotate keys regularly and store credentials in your host's secrets manager (AWS Secrets Manager, HashiCorp Vault, GitHub Actions secrets for CI).
- Use server-side encryption or S3 KMS if required.
- Audit S3 access logs for suspicious activity.
