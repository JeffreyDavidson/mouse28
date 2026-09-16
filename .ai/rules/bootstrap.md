---
paths:
  - bootstrap/app.php
---

# Bootstrap

## Let Forge Nginx resolve trusted client IPs
The current Forge topology resolves trusted Cloudflare proxies in Nginx and passes the result as REMOTE_ADDR. Do not enable blanket Laravel trustProxies('*'): client-supplied forwarded headers can undermine IP-based contact/newsletter throttles. Reassess trusted proxies explicitly if the hosting topology changes.
