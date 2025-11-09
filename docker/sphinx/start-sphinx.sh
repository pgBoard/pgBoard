#!/bin/sh

# Wait for database to be ready
echo "Waiting for database to be ready..."
sleep 10

# Create index directory if it doesn't exist
mkdir -p /opt/sphinx/index

# Build indexes
echo "Building Sphinx indexes..."
indexer --config /opt/sphinx/conf/sphinx.conf --all

# Start searchd
echo "Starting Sphinx daemon..."
searchd --config /opt/sphinx/conf/sphinx.conf --nodetach
