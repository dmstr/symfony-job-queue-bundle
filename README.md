<!-- file generated with AI assistance: Claude Code - 2026-06-09 19:27:00 UTC -->

# dmstr/symfony-job-queue-bundle

Persisted job tracking on top of Symfony Messenger.

## Features (planned)

- `Job` Doctrine entity with status, payload, timestamps, result
- `JobQueueService::dispatch()` — validate input + persist + dispatch to Messenger
- `JobMessage` / `JobMessageHandler` — async execution wrapper
- Status transitions: `pending → running → done | failed`
- `Job` exposed as read-only API Platform resource under `/admin/jobs`

## License

MIT © diemeisterei GmbH
