# Kubernetes Deployment Guide

## 📋 Wymagania

- Kubernetes 1.20+
- kubectl skonfigurowany
- cert-manager (dla SSL)
- Nginx Ingress Controller
- Docker registry (prywatny lub DockerHub)

## 🚀 Szybki start

### 1. Instalacja dependencji

```bash
# Nginx Ingress Controller
kubectl apply -f https://raw.githubusercontent.com/kubernetes/ingress-nginx/controller-v1.8.0/deploy/static/provider/cloud/deploy.yaml

# Cert-Manager (dla Let's Encrypt SSL)
helm repo add jetstack https://charts.jetstack.io
helm install cert-manager jetstack/cert-manager \
  --namespace cert-manager \
  --create-namespace \
  --set installCRDs=true
```

### 2. Zmienne konfiguracyjne

Edytuj `kubernetes/01-secrets.yaml` i zmień:
```yaml
DB_PASSWORD: "TWOJE-BEZPIECZNE-HASLO"
JWT_SECRET: "TWOJ-LOSOWY-STRING-MIN-32-ZNAKI"
```

Edytuj `kubernetes/04-backend.yaml` i `05-frontend.yaml`:
```yaml
image: your-registry/qrscanner-backend:latest  # Zmień your-registry
```

### 3. Build Docker images

```bash
# Backend
docker build -t your-registry/qrscanner-backend:latest \
  -f docker/Dockerfile.php .

# Frontend
docker build -t your-registry/qrscanner-frontend:latest \
  -f docker/Dockerfile.nginx .

# Push do rejestru
docker push your-registry/qrscanner-backend:latest
docker push your-registry/qrscanner-frontend:latest
```

### 4. Deploy na K8s

```bash
# Opcja 1: Automatycznie (z skryptem)
chmod +x kubernetes/deploy-k8s.sh
./kubernetes/deploy-k8s.sh

# Opcja 2: Ręcznie
kubectl apply -f kubernetes/00-namespace-configmap.yaml
kubectl apply -f kubernetes/01-secrets.yaml
kubectl apply -f kubernetes/02-persistent-volumes.yaml
kubectl apply -f kubernetes/03a-mysql-init-configmap.yaml
kubectl apply -f kubernetes/03-mysql.yaml
# Czekaj aż MySQL będzie ready
kubectl wait --for=condition=ready pod -l app=mysql -n qrscanner --timeout=300s
kubectl apply -f kubernetes/04-backend.yaml
kubectl apply -f kubernetes/05-frontend.yaml
kubectl apply -f kubernetes/07-phpmyadmin.yaml
kubectl apply -f kubernetes/06-ingress.yaml
kubectl apply -f kubernetes/08-cert-manager.yaml
```

### 5. Konfiguracja DNS

```bash
# Sprawdź IP Ingressu
kubectl get ingress -n qrscanner -o wide

# Dodaj do DNS:
# qrscanner.local -> INGRESS_IP
# phpmyadmin.qrscanner.local -> INGRESS_IP

# Lub dla /etc/hosts (local testing):
echo "INGRESS_IP qrscanner.local" >> /etc/hosts
echo "INGRESS_IP phpmyadmin.qrscanner.local" >> /etc/hosts
```

## 🔍 Monitoring

```bash
# Status podów
kubectl get pods -n qrscanner -o wide

# Status services
kubectl get svc -n qrscanner

# Status ingress
kubectl get ingress -n qrscanner -o wide

# Logi backendu
kubectl logs -n qrscanner -l app=backend -f

# Logi frontendu
kubectl logs -n qrscanner -l app=frontend -f

# Logi MySQL
kubectl logs -n qrscanner -l app=mysql -f

# Describe poda (dla debugowania)
kubectl describe pod -n qrscanner <pod-name>
```

## 🛠️ Scaling

```bash
# Zwiększ repliki backendu
kubectl scale deployment/backend --replicas=3 -n qrscanner

# Zwiększ repliki frontendu
kubectl scale deployment/frontend --replicas=3 -n qrscanner
```

## 📊 Resource Limits

Domyślnie ustawione:
- **Backend**: 256Mi RAM, 500m CPU
- **Frontend**: 256Mi RAM, 500m CPU
- **MySQL**: 512Mi RAM, 500m CPU

Edytuj w manifestach pod `resources:` jeśli potrzeba.

## 🔐 SSL Certificates

Certyfikaty są generowane automatycznie przez cert-manager + Let's Encrypt.

Status certyfikatów:
```bash
kubectl get certificate -n qrscanner
kubectl describe certificate qrscanner-tls -n qrscanner
```

Jeśli certyfikat nie generuje się (blokada rate limitingu):
```yaml
# Zmień w 08-cert-manager.yaml na staging (bez limitów)
server: https://acme-staging-v02.api.letsencrypt.org/directory
```

## 💾 Backup

```bash
# Backup bazy danych
kubectl exec -it mysql-xxxxx -n qrscanner -- \
  mysqldump -uroot -p<PASSWORD> qrscanner > backup.sql

# Backup wszystkich zasobów K8s
kubectl get all -n qrscanner -o yaml > qrscanner-backup.yaml
```

## 🗑️ Cleanup

```bash
# Usuń cały namespace (wszystkie zasoby)
kubectl delete namespace qrscanner

# Usuń tylko deployment
kubectl delete deployment backend -n qrscanner
```

## 📌 Port Forwarding (dla local testing)

```bash
# Frontend
kubectl port-forward svc/frontend-service 8080:80 -n qrscanner

# Backend
kubectl port-forward svc/backend-service 8081:80 -n qrscanner

# MySQL
kubectl port-forward svc/mysql-service 3306:3306 -n qrscanner
```

## 🆘 Troubleshooting

### Pod nie startuje
```bash
kubectl describe pod <pod-name> -n qrscanner
kubectl logs <pod-name> -n qrscanner
```

### Ingress nie robi się ready
```bash
kubectl describe ingress qrscanner-ingress -n qrscanner
# Sprawdź czy Nginx Ingress Controller jest zainstalowany
kubectl get pods -n ingress-nginx
```

### Certyfikat nie generuje się
```bash
# Sprawdź cert-manager logs
kubectl logs -n cert-manager deployment/cert-manager

# Sprawdź certificate status
kubectl describe certificate qrscanner-tls -n qrscanner
```

### MySQL nie inicjuje schemy
```bash
# Sprawdź init configmap
kubectl get configmap -n qrscanner
kubectl describe configmap mysql-init-script -n qrscanner

# Sprawdzenie bazy
kubectl exec -it mysql-xxxxx -n qrscanner -- mysql -uroot -p
mysql> show databases;
```

## 📚 Dodatkowe zasoby

- [Kubernetes Documentation](https://kubernetes.io/docs/)
- [Nginx Ingress Controller](https://kubernetes.github.io/ingress-nginx/)
- [Cert-Manager](https://cert-manager.io/)
- [kubectl Cheatsheet](https://kubernetes.io/docs/reference/kubectl/cheatsheet/)
