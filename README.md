# QR Scanner PWA

Aplikacja do zarządzania urządzeniami (np. windy, pompy) za pomocą kodów QR. PWA umożliwia pracę offline i instalację na urządzeniach mobilnych.

## 🎯 Cechy

- ✅ Generowanie kodów QR dla urządzeń
- ✅ Skanowanie kodów QR (web i mobilne)
- ✅ Zarządzanie danymi technicznymi urządzeń
- ✅ Historia badań technicznych i serwisu
- ✅ Kontrola dostępu (owner/technician/viewer)
- ✅ PWA - offline support, instalacja mobilna
- ✅ Responsive design
- ✅ Backend PHP + MySQL
- ✅ Dockeryzacja z Traefik SSL

## 📋 Wymagania

- Docker & Docker Compose
- Domena (np. qrscanner.local dla dev)
- SSL certificates (Let's Encrypt lub własne)

## 🚀 Szybki start

### Development

```bash
# 1. Klonuj repo
git clone <repo>
cd qrscanner

# 2. Kopiuj .env
cp .env.example .env

# 3. Zainstaluj zależności (opcjonalnie, Docker je pobierze)
cd backend && composer install
cd ../frontend && npm install

# 4. Uruchom Docker Compose
docker-compose up -d

# 5. Aplikacja dostępna pod http://localhost
```

### Production z Portainer/Kubernetes

```bash
# 1. Ustaw zmienne środowiska w .env.production
# Zmień hasła i JWT_SECRET!

# 2. Uruchom z Traefik SSL
docker-compose -f docker-compose.prod.yml up -d

# 3. Skonfiguruj DNS aby wskazywał na Twój serwer:
# qrscanner.local -> twoja.ip
# phpmyadmin.qrscanner.local -> twoja.ip
```

## 🏗️ Struktura projektu

```
qrscanner/
├── backend/                  # PHP Backend (Slim 4 + MySQL)
│   ├── src/
│   │   ├── Controllers/     # API endpoints
│   │   ├── Models/          # Data models
│   │   └── Middleware/      # Auth middleware
│   ├── config/              # Configuration
│   ├── public/              # Entry point (index.php)
│   ├── schema.sql           # Database schema
│   └── composer.json        # PHP dependencies
├── frontend/                # React PWA
│   ├── src/
│   │   ├── pages/           # Pages (Login, Dashboard, etc)
│   │   ├── components/      # React components
│   │   ├── api.js           # API client
│   │   ├── store.js         # State management (Zustand)
│   │   └── db.js            # Offline DB (Dexie)
│   ├── public/              # Static files + service-worker
│   └── package.json         # JS dependencies
├── docker/                  # Docker configs
│   ├── Dockerfile.php       # PHP-FPM image
│   ├── Dockerfile.nginx     # Nginx + React build
│   ├── nginx.conf
│   └── default.conf
├── docker-compose.yml       # Dev compose
├── docker-compose.prod.yml  # Prod compose z Traefik SSL
└── README.md
```

## 🔑 API Endpoints

### Autentykacja
- `POST /api/auth/register` - Rejestracja
- `POST /api/auth/login` - Logowanie (zwraca JWT token)

### Urządzenia (publiczne)
- `GET /api/devices/{id}` - Pobierz dane urządzenia (publiczne)
- `GET /api/devices/{id}/full` - Pełne dane + rekordy

### Urządzenia (protected - wymaga JWT)
- `POST /api/devices` - Utwórz urządzenie
- `GET /api/devices` - Lista moich urządzeń
- `PUT /api/devices/{id}` - Edytuj urządzenie
- `DELETE /api/devices/{id}` - Usuń urządzenie
- `POST /api/devices/{id}/generate-qr` - Wygeneruj kod QR

### Rekordy techniczne (protected)
- `POST /api/devices/{id}/records` - Dodaj rekord
- `GET /api/devices/{id}/records` - Lista rekordów

## 🔐 Kontrola dostępu

### Role użytkowników

1. **Owner** - Wszystkie uprawnienia
2. **Technician** - Może edytować wszystkie urządzenia, dodawać rekordy
3. **Viewer** - Może tylko czytać dane (domyślna rola)

### Logika uprawnień

```
Czytanie publiczne: ✅ Każdy może skanować kod QR i czytać dane
Edycja: ⛔ Tylko owner/technician danego urządzenia
```

## 🗄️ Baza danych

### Tabele

**users**
- id, email, password, name, role, created_at

**devices**
- id, name, type, location, manufacturer, serial_number, install_date, qr_code, user_id

**technical_records**
- id, device_id, user_id, record_date, record_type (inspection/maintenance/repair/testing), description, technician, notes

## 📱 PWA Features

- Service Worker dla offline support
- Installable na urządzeniach mobilnych
- Dexie.js dla offline storage
- Synchronizacja danych z backendem gdy jest connection

## 🔒 SSL/HTTPS

### Development (self-signed)
```bash
# Certyfikat jest generowany automatycznie przez Traefik
```

### Production (Let's Encrypt)
```bash
# Traefik automatycznie zarządza certyfikatami Let's Encrypt
# Zmień email w docker-compose.prod.yml na swój
```

## 📦 Deployment na Portainer

1. **Create Stack** z `docker-compose.prod.yml`
2. **Set Environment Variables**:
   - Wszystkie zmienne z `.env.production`
3. **Deploy**
4. **Czekaj na Pull obrazów i start**
5. **Skonfiguruj reverse proxy** (jeśli nie używasz Traefika)

## 🐛 Troubleshooting

### Baza danych nie inicjuje
```bash
# Usuń volume i spróbuj ponownie
docker-compose down -v
docker-compose up -d
```

### Frontend nie łączy się z API
```bash
# Sprawdź REACT_APP_API_URL w .env
# Sprawdź network między kontenerami
docker-compose logs frontend
```

### Problemy z SSL
```bash
# Sprawdź certyfikaty w letsencrypt/
# Logs Traefika
docker logs qrscanner_traefik
```

## 📝 TODO / Przyszłe features

- [ ] Backup automatyczny bazy danych
- [ ] Email notifications dla serwisantów
- [ ] Historyk zmian (audit log)
- [ ] Export danych do PDF
- [ ] Multilingual support
- [ ] Dark mode
- [ ] Mobile app (React Native)

## 📄 License

MIT

## 👨‍💻 Support

Dla pytań lub błędów - create issue na GitHub
