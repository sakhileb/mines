Queue worker deployment guidance

Use one of the following approaches to run Laravel queue workers in production.

1) Supervisor (recommended for multiple processes)
- Copy `deploy/queue-worker.supervisord.conf` to your supervisor config folder (e.g. `/etc/supervisor/conf.d/mines-queue.conf`).
- Reload supervisor: `supervisorctl reread && supervisorctl update`.

2) Systemd service
- Copy `deploy/queue-worker.service` to `/etc/systemd/system/mines-queue-worker.service`.
- Enable and start:
  ```
  systemctl daemon-reload
  systemctl enable --now mines-queue-worker.service
  ```

Security notes
- Run queue workers under a restricted user (example uses `www-data`).
- Monitor logs in `/var/log/mines/queue-worker.log` and configure logrotate.
- Ensure `QUEUE_CONNECTION` in environment is set to a worker-backed driver (`redis`, `sqs`, `database`) and not `sync`.

Mail configuration
- Configure `MAIL_MAILER` (SMTP or transactional provider) in your host environment, not in git. Example options:
  - `MAIL_MAILER=smtp` with `MAIL_HOST`, `MAIL_PORT`, `MAIL_USERNAME`, `MAIL_PASSWORD`
  - Use third-party providers (SendGrid, Mailgun) and set credentials via your host's secrets manager.
