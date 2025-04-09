# Create a temporary directory
mkdir -p temp-project

# Create the Symfony project in the temp directory
docker/bin/composer create-project symfony/skeleton:"6.4.*" temp-project

# Move all files (including hidden ones) from temp to current directory
mv temp-project/* .
mv temp-project/.* . 2>/dev/null || true # The || true handles the error when moving . and ..

# Remove temp directory
rmdir temp-project
