#!/bin/bash

# QR Scanner - Kubernetes Deployment Script
# Usage: ./deploy-k8s.sh

set -e

NAMESPACE="qrscanner"
REGISTRY="your-registry"
IMAGE_TAG="latest"

echo "🚀 Deploying QR Scanner to Kubernetes..."

# 1. Create namespace and base resources
echo "📦 Creating namespace and ConfigMaps..."
kubectl apply -f kubernetes/00-namespace-configmap.yaml

# 2. Create secrets (pamiętaj zmienić hasła!)
echo "🔐 Creating secrets..."
kubectl apply -f kubernetes/01-secrets.yaml

# 3. Create persistent volumes
echo "💾 Creating persistent volumes..."
kubectl apply -f kubernetes/02-persistent-volumes.yaml

# 4. Deploy MySQL
echo "🗄️  Deploying MySQL..."
kubectl apply -f kubernetes/03a-mysql-init-configmap.yaml
kubectl apply -f kubernetes/03-mysql.yaml

# Wait for MySQL to be ready
echo "⏳ Waiting for MySQL to be ready..."
kubectl wait --for=condition=ready pod -l app=mysql -n $NAMESPACE --timeout=300s || true
sleep 10

# 5. Build and push Docker images (jeśli potrzebne)
echo "🐳 Building Docker images..."
docker build -t $REGISTRY/qrscanner-backend:$IMAGE_TAG \
  -f docker/Dockerfile.php .

docker build -t $REGISTRY/qrscanner-frontend:$IMAGE_TAG \
  -f docker/Dockerfile.nginx .

# Opcjonalnie: push do rejestru
# docker push $REGISTRY/qrscanner-backend:$IMAGE_TAG
# docker push $REGISTRY/qrscanner-frontend:$IMAGE_TAG

# 6. Deploy backend and frontend
echo "🚀 Deploying backend and frontend..."
kubectl apply -f kubernetes/04-backend.yaml
kubectl apply -f kubernetes/05-frontend.yaml
kubectl apply -f kubernetes/07-phpmyadmin.yaml

# 7. Deploy Ingress
echo "🌐 Deploying Ingress..."
kubectl apply -f kubernetes/06-ingress.yaml

# 8. Deploy cert-manager SSL (jeśli nie zainstalowany)
echo "🔒 Deploying cert-manager..."
kubectl apply -f kubernetes/08-cert-manager.yaml

echo ""
echo "✅ Deployment complete!"
echo ""
echo "📊 Check status:"
echo "   kubectl get pods -n $NAMESPACE"
echo "   kubectl get svc -n $NAMESPACE"
echo "   kubectl get ingress -n $NAMESPACE"
echo ""
echo "🔍 Logs:"
echo "   kubectl logs -n $NAMESPACE -l app=backend"
echo "   kubectl logs -n $NAMESPACE -l app=frontend"
echo ""
echo "⚠️  PAMIĘTAJ:"
echo "   1. Zmień DB_PASSWORD i JWT_SECRET w kubernetes/01-secrets.yaml"
echo "   2. Zmień image registry w kubernetes/04-backend.yaml i 05-frontend.yaml"
echo "   3. Skonfiguruj DNS by wskazywał na Ingress IP"
echo "   4. Zainstaluj cert-manager: helm repo add jetstack https://charts.jetstack.io && helm install cert-manager jetstack/cert-manager --namespace cert-manager --create-namespace --set installCRDs=true"
