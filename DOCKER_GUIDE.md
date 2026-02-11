# Docker Setup Guide for Laravel Real-Time Chat App

This guide explains how to install Docker and run the project both locally and on a production server.

## Prerequisites

1.  **Docker**: Engine and Compose installed.
2.  **Git**: To clone the repository.

---

## 1. Install Docker

### Windows
1.  Download **Docker Desktop** from [docker.com](https://www.docker.com/products/docker-desktop/).
2.  Run the installer and follow the instructions.
3.  Ensure WSL 2 is enabled if prompted.
4.  Restart your computer.

### Linux (Ubuntu Server)
Run the following commands:
```bash
# Update packages
sudo apt-get update

# Install certificates
sudo apt-get install ca-certificates curl gnupg

# Add Docker's official GPG key
sudo install -m 0755 -d /etc/apt/keyrings
curl -fsSL https://download.docker.com/linux/ubuntu/gpg | sudo gpg --dearmor -o /etc/apt/keyrings/docker.gpg
sudo chmod a+r /etc/apt/keyrings/docker.gpg

# Set up repository
echo \
  "deb [arch="$(dpkg --print-architecture)" signed-by=/etc/apt/keyrings/docker.gpg] https://download.docker.com/linux/ubuntu \
  "$(. /etc/os-release && echo "$VERSION_CODENAME")" stable" | \
  sudo tee /etc/apt/sources.list.d/docker.list > /dev/null

# Install Docker Engine
sudo apt-get update
sudo apt-get install docker-ce docker-ce-cli containerd.io docker-buildx-plugin docker-compose-plugin

# Enable Docker
sudo systemctl enable docker
sudo systemctl start docker
```

---

## 2. Project Setup & Configuration

### Step 1: Clone the Project
```bash
git clone <your-repo-url>
cd chating-app
```

### Step 2: Configure Environment
Copy the example environment file:
```bash
cp .env.example .env
```

**IMPORTANT:** Update `.env` to match the Docker service names and credentials.

For **Docker**, update these specific lines in `.env`:

```env
# Database Config for Docker
DB_CONNECTION=mysql
DB_HOST=db             # Must match service name in docker-compose.yml
DB_PORT=3306
DB_DATABASE=chating_app
DB_USERNAME=root
DB_PASSWORD=root       # Or whatever you set in docker-compose.yml

# Reverb Config for Docker
REVERB_APP_ID=335386
REVERB_APP_KEY=zgl1k0uavq7j08iit7za
REVERB_APP_SECRET=k6vvr1pxryuag5s4iyv1
REVERB_HOST="0.0.0.0"  # Listen on all interfaces inside container
REVERB_PORT=8081
REVERB_SCHEME=http

# Vite Config (Frontend)
VITE_REVERB_HOST="localhost" # Or your server IP/Domain
VITE_REVERB_PORT=8081
VITE_REVERB_SCHEME=http
```

---

## 3. Running the Project (Local & Server)

The commands are the same for both local development and server deployment.

### Step 1: Build and Start Containers
```bash
docker compose up -d --build
```
* `-d`: Runs in detached mode (background).
* `--build`: Forces a rebuild of images.

### Step 2: Install Dependencies inside Container
Now that the containers are running, execute commands inside the `app` container:

```bash
# Install PHP dependencies
docker compose exec app composer install

# Generate Application Key
docker compose exec app php artisan key:generate
```

### Step 3: Database Migration & Seeding
```bash
docker compose exec app php artisan migrate --seed
```
* This creates the tables and installs the test users.

### Step 4: Install & Build Frontend Assets
You can do this on your host machine if you have Node installed, or inside the container:

**Option A: Inside Container (Recommended)**
```bash
docker compose exec app npm install
docker compose exec app npm run build
```

**Option B: On Host Machine**
```bash
npm install
npm run build
```

### Step 5: Verify Running Services
```bash
docker compose ps
```
You should see `app`, `webserver`, `db`, and `reverb` all running.

---

## 4. Accessing the Application

*   **Web App**: Open `http://localhost` (or your Server IP).
*   **Database**: Port `33061` (user: root, pass: root).
*   **Reverb WebSocket**: Port `8081`.

## Troubleshooting

**Reverb Port Issue:**
If Reverb fails to start, make sure port `8081` is free on your host.

**Permissions:**
If you encounter permission errors on Linux:
```bash
sudo chown -R $USER:$USER .
docker compose exec app chown -R www-data:www-data /var/www/storage
```

**Restarting:**
To restart everything:
```bash
docker compose restart
```

**Stopping:**
```bash
docker compose down
```
