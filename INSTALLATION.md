## Instrukcja instalacji dependencji

### Backend (PHP)

```bash
cd backend

# Zainstaluj Composer (jeśli nie masz)
# Pobranie z https://getcomposer.org/download/

# Zainstaluj dependencje
composer install

# Kopiuj .env
cp ../.env .env

# Gotowe!
```

### Frontend (Node.js)

```bash
cd frontend

# Upewnij się że masz Node.js 18+ (sprawdź: node --version)
# Pobranie z https://nodejs.org/

# Zainstaluj dependencje
npm install

# Build production version
npm run build

# Lub development server
npm start
```

### Docker (Rekomendowane)

```bash
# Zainstaluj Docker Desktop
# https://www.docker.com/products/docker-desktop

# Docker automatycznie pobierze wszystkie dependencje
docker-compose up -d

# To wszystko!
```

## 🖥️ System requirements

- **CPU**: 2+ cores
- **RAM**: 4+ GB
- **Storage**: 20+ GB
- **Ports**: 80, 443, 3306

## 🐧 Linux Installation

```bash
# Ubuntu/Debian
sudo apt-get update
sudo apt-get install -y docker.io docker-compose
sudo usermod -aG docker $USER

# Restart
newgrp docker

# Test
docker --version
docker-compose --version
```

## 🎯 Next steps

Przejdź do README.md dla instrukcji uruchamiania!
