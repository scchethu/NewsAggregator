# News Aggregator API

## Overview

The News Aggregator API is a RESTful service built with Laravel that aggregates articles from various news sources. This API provides endpoints for frontend applications to fetch news articles, filter by categories, and search by keywords.

## Features

- Fetch articles from multiple news sources
- Filter articles by category
- Search articles by keywords
- Pagination for large sets of articles
- Caching for improved performance

## Prerequisites

- [Docker](https://www.docker.com/get-started) (installed on your machine)
- [Docker Compose](https://docs.docker.com/compose/) (if using the optional setup)
## Requirements

- PHP >= 7.4
- Composer
- Laravel >= 8.x
- MySQL or any other supported database

## Installation

1. **Clone the repository:**

   ```bash
   git clone https://github.com/scchethu/NewsAggregator.git
   cd NewsAggregator

2. **Build and Run the Docker Containers:**

   ```bash
    docker-compose up -d

