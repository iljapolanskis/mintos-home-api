#!/bin/bash
# bin/project-structure

# Define output colors
GREEN='\033[0;32m'
BLUE='\033[0;34m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

echo -e "${GREEN}==== Symfony Project Structure ====${NC}\n"

# Function to print directories with some formatting
print_dir_structure() {
    local dir=$1
    local exclude=$2
    local depth=$3
    local include_patterns=$4

    find_args=("$dir" -type d -not -path "*/vendor/*" -not -path "*/var/*" -not -path "*/node_modules/*" -not -path "*/.git/*" -not -path "*/.idea/*" -not -path "*/docker/*")

    if [ -n "$exclude" ]; then
        for pattern in $exclude; do
            find_args+=(-not -path "*/$pattern/*")
        done
    fi

    if [ -n "$depth" ]; then
        find_args+=(-maxdepth "$depth")
    fi

    echo -e "${BLUE}Directory Structure:${NC}"
    find "${find_args[@]}" | sort | sed -e "s/[^-][^\/]*\// │   /g" -e "s/│ /├── /g"
    echo ""
}

# Function to show Symfony bundle info
show_symfony_info() {
    # Check if we're in a Docker environment
    if command -v docker-compose > /dev/null; then
        echo -e "${BLUE}Symfony Information:${NC}"
        docker-compose exec -T php php bin/console about 2>/dev/null || echo "Symfony console not available"
        echo ""
    fi
}

# Function to show Docker services
show_docker_info() {
    if [ -f "docker-compose.yml" ]; then
        echo -e "${BLUE}Docker Services:${NC}"
        docker-compose ps
        echo ""
    fi
}

# Function to show custom scripts
show_custom_scripts() {
    echo -e "${BLUE}Custom Scripts:${NC}"
    if [ -d "bin" ]; then
        find bin -type f -executable -not -path "*/vendor/*" | sort | sed 's/^/├── /'
    else
        echo "No custom bin scripts found"
    fi
    echo ""
}

# Main execution
print_dir_structure "." "" "3"
show_custom_scripts
show_docker_info
show_symfony_info

echo -e "${YELLOW}Note: vendor/, var/, node_modules/, .git/, .idea/, and docker/ directories are excluded for clarity${NC}"
