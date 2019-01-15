# Kinko [![Build Status](https://semaphoreci.com/api/v1/noeldemartin/kinko/branches/master/badge.svg)](https://semaphoreci.com/noeldemartin/kinko)

Kinko is a server implementation to provide storage for [Autonomous Data](https://noeldemartin.github.io/autonomous-data) applications.

⚠️ This project is a *work in progress*, and it has been paused to focus on developing applications using the [Solid](https://solid.mit.edu) protocol instead. For research purposes, you can continue reading to find more about the current status of the project.

## Protocol

Kinko is being built as an implementation of a yet unnamed protocol, so that other implementations can be built to be compatible with client apps.

The protocol is based on [OAuth](https://oauth.net) and [GraphQL](https://graphql.org) protocols.

Client applications are registered using OAuth's [dynamic client registration](https://tools.ietf.org/html/rfc7591), where app capabilities are described. A GraphQL schema is provided indicating the models that will be used, together with other information like application name and description. The authorization screen should display this information so that users know how the application will interact with their data, and they'll be able to limit or modify those capabilities (similar to OAuth's permission scopes).

Once the registration is complete, CRUD operations can be performed using a GraphQL endpoint.

The GraphQL schema supports multiple extensions that make it easy to work with the platform. For example an `@auto` directive to populate IDs and timestamps, or custom types like `User` to leverage existing information within the platform.

In order to increase compatibility between apps, a system is necessary that can map data between schemas. This hasn't been specified yet, but [RDFS](https://en.m.wikipedia.org/wiki/RDF_Schema) is a strong contender.

## Implementation

The project is implemented using [Laravel](https://laravel.com). With [Vue](https://vuejs.org) in the frontend and [MongoDB](https://www.mongodb.com) for data persistence.

The current implementation supports:

- User registration using the `kinko:register` artisan command.
- Client application registration posting to the `/store/register` endpoint.
- User authentication visiting `/login`.
- User authorization using the `/store/authorize` url.
- Basic CRUD operations posting to the `/store` graphql endpoint.

For more information on implementation details and how to interact with the server, take a look at the tests that can be found under `/tests/Integration`.

## Client Applications

One client application compatible with this server can be found on the following repository: [https://github.com/NoelDeMartin/focus](https://github.com/NoelDeMartin/focus)
