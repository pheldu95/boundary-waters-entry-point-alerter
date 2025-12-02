https://entrypointalerter.app/
# About
The Boundary Wates has gotten very popular over the last few years. It can often be hard to get a permit for the area that you want to go to if you don't buy it early in the year. This web app will allow you to track entry point lakesx that you want to go to on a specific date. Once you are tracking one or more entry points, you will get an email when a permit becomes available.

# To do:
1. I don't like the name PermitWatch entity. Want to change it to TrackedEntryPoint.

## Getting Started

1. If not already done, [install Docker Compose](https://docs.docker.com/compose/install/) (v2.10+)
2. Run `docker compose build --pull --no-cache` to build fresh images
3. Run `docker compose up --wait` to set up and start a fresh Symfony project
4. Open `https://localhost` in your favorite web browser and [accept the auto-generated TLS certificate](https://stackoverflow.com/a/15076602/1352334)
5. Run `docker compose down --remove-orphans` to stop the Docker containers.
6. 
# My Notes

Using mailtrap for testing email sending https://symfonycasts.com/screencast/mailtrap/mailtrap-email-testing#play

For production:
https://symfonycasts.com/screencast/mailtrap/production-sending-mailtrap
