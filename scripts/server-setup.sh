#!/bin/bash
# Server setup script for auto-deploy
# Run this once on your server to prepare for GitHub Actions deployments

set -e

echo "=========================================="
echo "Server Setup for Auto-Deploy"
echo "=========================================="
echo ""

# Check if running as root (recommended for some steps)
if [[ $EUID -ne 0 ]]; then
   echo "⚠️  Not running as root. Some steps may require sudo."
fi

# 1. Create deploy user (optional, recommended)
read -p "Create a dedicated 'deploy' user? (y/n) " -n 1 -r
echo
if [[ $REPLY =~ ^[Yy]$ ]]; then
    if ! id "deploy" &>/dev/null; then
        echo "Creating 'deploy' user..."
        sudo useradd -m -s /bin/bash deploy
        echo "✅ User 'deploy' created"
    else
        echo "ℹ️  User 'deploy' already exists"
    fi
fi

# 2. Get the deploy directory
read -p "Enter deploy directory path (e.g., /var/www/html/agco): " DEPLOY_PATH

if [ -z "$DEPLOY_PATH" ]; then
    echo "❌ Deploy path cannot be empty"
    exit 1
fi

# 3. Create directory and set permissions
echo "Setting up deploy directory: $DEPLOY_PATH"
sudo mkdir -p "$DEPLOY_PATH"
sudo chown -R deploy:deploy "$DEPLOY_PATH" 2>/dev/null || sudo chown -R www-data:www-data "$DEPLOY_PATH"
sudo chmod 755 "$DEPLOY_PATH"
echo "✅ Directory created and permissions set"

# 4. Set up SSH authorized_keys for deploy user
echo ""
echo "Setting up SSH authorized_keys..."
DEPLOY_HOME=$(eval echo "~deploy")
SSH_DIR="$DEPLOY_HOME/.ssh"
sudo mkdir -p "$SSH_DIR"
sudo chmod 700 "$SSH_DIR"
sudo touch "$SSH_DIR/authorized_keys"
sudo chmod 600 "$SSH_DIR/authorized_keys"
sudo chown -R deploy:deploy "$SSH_DIR" 2>/dev/null || echo "⚠️  Could not set owner to deploy user"

echo ""
echo "📝 Add your public SSH key:"
read -p "Paste the contents of deploy_key.pub (or press Enter to skip): " PUB_KEY

if [ ! -z "$PUB_KEY" ]; then
    echo "$PUB_KEY" | sudo tee -a "$SSH_DIR/authorized_keys" > /dev/null
    echo "✅ Public key added to authorized_keys"
fi

# 5. Verify required tools
echo ""
echo "Checking for required tools..."

if ! command -v unzip &> /dev/null; then
    echo "❌ unzip not found. Installing..."
    sudo apt-get update && sudo apt-get install -y unzip || sudo yum install -y unzip
    echo "✅ unzip installed"
else
    echo "✅ unzip found"
fi

if command -v php &> /dev/null; then
    echo "✅ PHP $(php -v | head -n 1)"
else
    echo "⚠️  PHP not found (optional)"
fi

if command -v composer &> /dev/null; then
    echo "✅ Composer found"
else
    echo "⚠️  Composer not found (optional)"
fi

if command -v mysql &> /dev/null; then
    echo "✅ MySQL client found"
else
    echo "⚠️  MySQL client not found (optional)"
fi

# 6. Create .ssh directory structure if needed
if [ ! -d "$DEPLOY_PATH/.ssh" ]; then
    sudo mkdir -p "$DEPLOY_PATH/.ssh"
    sudo chmod 700 "$DEPLOY_PATH/.ssh"
fi

# 7. Summary
echo ""
echo "=========================================="
echo "✅ Server Setup Complete!"
echo "=========================================="
echo ""
echo "Deploy user: deploy"
echo "Deploy directory: $DEPLOY_PATH"
echo "SSH dir: $SSH_DIR"
echo ""
echo "Next steps on your local machine:"
echo "  1. Copy deploy_key.pub contents above to deploy user's authorized_keys"
echo "  2. Add these secrets to GitHub:"
echo "     DEPLOY_HOST: $(hostname -I | awk '{print $1}')"
echo "     DEPLOY_USER: deploy"
echo "     DEPLOY_PORT: 22"
echo "     DEPLOY_PATH: $DEPLOY_PATH"
echo "     DEPLOY_SSH_KEY: (contents of deploy_key private key)"
echo ""
echo "  3. Copy deploy.sh to: $DEPLOY_PATH/deploy.sh"
echo "  4. Make it executable: chmod +x $DEPLOY_PATH/deploy.sh"
echo "  5. Test with: git push origin main"
echo ""
