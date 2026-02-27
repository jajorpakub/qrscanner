# QR Scanner - Instrukcja Deploymentu na Portainer/K8s

## 🎯 Cel
Wdrożenie aplikacji QR Scanner na Portainerze z MySQL, SSL/HTTPS i reverse proxy.

## 📋 Checklist

- [x] PHP Backend API (Slim 4)
- [x] React Frontend PWA
- [x] MySQL Database
- [x] Nginx + React build
- [x] Traefik reverse proxy z SSL
- [x] Docker Compose (dev & prod)
- [ ] Portainer Stack Configuration

## 🚀 Kroki wdrażania

### 1. Przygotowanie

```bash
# Na Twoim serwerze z Portainer
cd /opt/qrscanner
git clone <repo>
cd qrscanner

# Skopiuj zmienne dla produkcji
cp .env.production .env

# WICHTIG: Zmień te wartości!
# - DB_PASSWORD
# - JWT_SECRET
# - Wszystkie domeny
```

### 2. Portainer Stack

W Portainer UI:

1. **Stacks** → **Add Stack**
2. **Web Editor** → Wklej zawartość `docker-compose.prod.yml`
3. **Environment Variables**:
   ```
   DB_PASSWORD=twoje-bezpieczne-haslo
   JWT_SECRET=twoj-sekretny-klucz-jwt
   QR_DOMAIN=https://qrscanner.example.com
   REACT_APP_API_URL=https://qrscanner.example.com/api
   ```
4. **Deploy**

### 3. DNS Configuration

Dodaj do DNS serwera:
```
qrscanner.example.com     A  twoj.serwer.ip
phpmyadmin.qrscanner.com A  twoj.serwer.ip
```

### 4. Sprawdzenie uruchamiania

```bash
# SSH na serwer
docker ps # Powinno pokazać 5 kontenerów (mysql, phpmyadmin, backend, frontend, traefik)

# Sprawdź logi
docker logs qrscanner_traefik
docker logs qrscanner_backend
docker logs qrscanner_frontend

# Test healthcheck
curl http://localhost/health
curl http://localhost/api/health
```

### 5. Traefik SSL

Traefik automatycznie:
- ✅ Pobrze certyfikat z Let's Encrypt
- ✅ Konfiguruje HTTPS
- ✅ Redirectuje HTTP → HTTPS

Jeśli chcesz własne certyfikaty:

```bash
# Umieść w ./letsencrypt/
cp cert.pem letsencrypt/
cp key.pem letsencrypt/
```

### 6. Pierwsza konfiguracja

1. Otwórz https://qrscanner.example.com
2. **Zarejestruj się** - zmień role na "owner"
3. Utwórz urządzenie
4. Wygeneruj QR code
5. Test skanowania: https://qrscanner.example.com/scan

## 🔑 Zmienne środowiskowe (WAŻNE!)

```env
# Database
DB_PASSWORD=ZMIEN_NA_SILNE_HASLO   # Minimum 16 znaków
DB_NAME=qrscanner

# JWT Token Secret - zmień!
JWT_SECRET=zmien-to-na-losowy-string-min-32-znaki

# Domeny
QR_DOMAIN=https://qrscanner.example.com
REACT_APP_API_URL=https://qrscanner.example.com/api

# Environment
APP_ENV=production
APP_DEBUG=false
```

## 🔐 Bezpieczeństwo

### Zalecane kroki:

1. **Zmień defaultowe hasła**
   ```bash
   # PhpMyAdmin access
   # Użytkownik: root
   # Hasło: DB_PASSWORD (z .env)
   ```

2. **Włącz SSL/TLS** ✅ Traefik to robi

3. **Setup Firewall**
   ```bash
   # Zezwalaj tylko na:
   # :80/tcp (HTTP redirect)
   # :443/tcp (HTTPS)
   # :3306 (MySQL - tylko dla backendu!)
   ```

4. **Backup bazy danych**
   ```bash
   # Codziennie o 2 AM
   docker exec qrscanner_mysql mysqldump -uroot -p<HASLO> qrscanner > backup-$(date +%Y%m%d).sql
   ```

## 📊 Monitoring

### Traefik Dashboard
```
http://localhost:8080/dashboard/
```

### Baza danych
```
https://phpmyadmin.qrscanner.example.com
```

## 🛠️ Troubleshooting

### Problem: Traefik nie generuje certyfikatów
```bash
# Sprawdź logi
docker logs qrscanner_traefik

# Zmień na staging Let's Encrypt (bez limitów)
# https://acme-staging-v02.api.letsencrypt.org/directory
```

### Problem: MySQL nie uruchamia się
```bash
# Sprawdź uprawnienia
docker exec qrscanner_mysql ls -la /var/lib/mysql

# Usuń volume i zacznij od nowa
docker-compose down -v
docker-compose -f docker-compose.prod.yml up -d mysql
```

### Problem: Frontend zwraca 404
```bash
# Sprawdź build
docker logs qrscanner_frontend | grep "build"

# Sprawdź czy index.html jest w nginx
docker exec qrscanner_frontend ls -la /usr/share/nginx/html/
```

## 📈 Scaling (Kubernetes)

Jeśli chcesz Kubernetes zamiast Docker Compose:

```bash
# Konwertuj docker-compose na K8s manifests
# Używając: kompose convert -f docker-compose.prod.yml

# Lub użyj Helm Chart (przyszłe wersje)
```

## ✅ Checklist po deploymencie

- [ ] ✅ App dostępna na HTTPS
- [ ] ✅ Można się zalogować
- [ ] ✅ Można stworzyć urządzenie
- [ ] ✅ Można wygenerować QR
- [ ] ✅ Skanowanie QR działa
- [ ] ✅ PhpMyAdmin dostępne
- [ ] ✅ SSL certyfikat ważny
- [ ] ✅ Backup bazy skonfigurowany

## 🆘 Support

Błędy? Sprawdź:
1. Docker logs: `docker-compose logs -f`
2. Network: `docker network ls`
3. Zmienne: `docker exec <container> env | grep DB_`
