---
paths:
  - '{app/Models/Episode.php,app/Filament/Resources/Episodes/**,resources/views/episodes/**,config/podcast.php}'
---

# Episodes

## Use Transistor as the podcast host
Transistor owns podcast MP3 hosting, the canonical RSS feed, and embedded episode players. Store each episode's https://share.transistor.fm/s/... URL and derive only allowlisted Transistor embed URLs; do not restore site-hosted MP3 upload controls or a generated first-party podcast feed.
