# Realtime

Velo pushes data changes to connected clients in real-time using WebSockets.
By default velo supports two main driver, Laravel Reverb and Pusher. You can choose your own driver and setup your frontend approach.

## Subscribing to Changes

Clients can subscribe to specific collections to receive updates when records are created, updated, or deleted.

### Endpoint
`POST /api/realtime/subscribe`

Payload:
```json
{
    "collection": "posts",
    "filter": "id = '...'", // (optional)
    "socket_id": "..."
}
```

- `collection`: (Required) The name or ID of the collection to subscribe to.
- `filter`: (Optional) A filter expression to only receive updates for matching records.
- `socket_id`: (Optional) The socket ID of the client.

Filter here works as regular query filter string, you can filter what data you want to receive or what not.

### Channels

The subscribe endpoint returns a unique `channel_name`. Clients should use this channel name to listen for events.

Format: `{prefix}{uuid}`

*Channel prefix is configurable in `config/velo.php` (default is `velo.realtime.`).*

## Events

When data changes, an event is broadcasted to the channel.

| Event | Payload |
| :--- | :--- |
| `create` | `{"action": "create", "record": {...}}` |
| `update` | `{"action": "update", "record": {...}}` |
| `delete` | `{"action": "delete", "id": "..."}` |
