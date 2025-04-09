# Install project in temporary directory & move back
mkdir -p temp-project

docker/bin/composer create-project symfony/skeleton:"6.4.*" temp-project

mv temp-project/* .
mv temp-project/.* . 2>/dev/null || true # The || true handles the error when moving . and ..

rmdir temp-project
