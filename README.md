# cache-API

The backend for cache API for PETA latino donation form

## Features

- Creates a new text file that contains the data for the currency rates
- The cache text files last one day before calling in the fixer.io API to prevent calling the fixer.io API multiple times when the donation form for the PETA latino loads
- Does not create a new text file if the URL is invalid or it has reached the limit to call the API
- Echoes the error when the cache API has an error

## Deployment

It's going to be deployed in the AWS 