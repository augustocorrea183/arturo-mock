# Arturo Mock

Simple mock tool for developers.

- Fast setup
- UI for CRUD of mocks
- Record mocks
- Forward rules (proxy)
- MondoDB/HD storage

## Requirements

- PHP 7
- Composer
- Or docker-compose

## Run

In root path, using docker-compose you only need type:
```
docker-compose up
```
Done!.

## Getting started

In your browser type: http://localhost/__admin/

For example, if you want to create a new mock in /products/car 

Create a new mock (button), eg:

- Title: My description
- Url: /products/car
- Proxy: 
- Action: enable
- Method: get
- Content Type: application/json
- Status Code: 200
- Body: {"product": "a car"}

Go to your browser and type:

http://localhost/products/car

The response will be:

```
{
  "product": "a car" 
}
```
Done!

## Documentation

### Basic concepts

- You can create all the mocks you want for the same uri and method but they must be disable.
- You can only create/use one mock with action enable/proxy/record for same uri and method at same time.

### Record a mock

In your browser type: http://localhost/__admin/

For example, if you want to create a new mock of http://one_server_host/api/product/1

Create a new mock (button), eg:

- Title: My description
- Url: /api/product/1
- Proxy: http://one_server_host/api/product/car
- Action: record
- Method: get
- Content Type: ...
- Status Code: here put status code with response
- Body: nothing

Go to your browser and type:

http://localhost/api/product/car

You watch the response of the API, for example:

```
{
  "product": "a car" 
}
```

Now, go to the admin and refresh the page. Go to your new mock (click in the title). In the body now you have {"product": "a car"}.

Now you can edit the mock and change the action to "enable".

### Use as proxy

Create a new mock, eg:

- Title: My description
- Url: /api/products/*
- Proxy: http://one_server_host/api/products/
- Action: proxy
- Method: get
- Content Type: ...
- Status Code: here put status code with response
- Body: nothing

If you make a request to http://localhost/api/products/cats?color=black, the proxy will forward the request to http://one_server_host/api/products/cats?color=black

The asterisk indicates that everything after it will be added at the end of the url.

