## Nginx setup (Docker)

### HTTPS certificates

Generate locally trusted HTTPS certificates, using `mkcert`:

```bash
cd .docker/nginx/ssl

CAROOT=$(pwd) mkcert -install
# Optional, if not stored on the system already
#sudo security add-trusted-cert -d -r trustRoot -k /Library/Keychains/System.keychain "$(pwd)/rootCA.pem"

domain=precontentieux.anje-justice.dev
# Create certificates for domain
CAROOT=$(pwd) mkcert "${domain}"
# Register domain as local domain
echo "127.0.0.1 ${domain}" | sudo tee -a /etc/hosts
```
