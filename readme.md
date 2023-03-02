# Short description API for Wikimedia take-home assignment

### Notes
- I used PHP 7.4 as that's what I have running on my personal laptop right now
- tests require PHPUnit (I used 9.6), but that should be the only dependency

## I/O Schema
- `index.php` accepts a `title` query parameter, which will be the search term
- returns a JSON object, which will always have the field `ok`
- depending on whether `ok` is true or false, the object will additionally have either a `short description` or `error` field, corresponding to the short description or error message if the request failed

## Running Locally
- `php -S localhost:8080`
- `http://localhost:8080?title=Yoshua%20bengio`
- that's it!

## Scalability
Since this is a passthrough service, scaling it to be able to handle a large number of users is relatively easy, provided the underlying Wikipedia APIs can handle the increased traffic.  As the core functionality of the service is running a script that does not interact with other threads or requests, we can provision a fleet of hosts relative to the size of traffic expected, or use autoscaling to ensure we always have enough computational resources.  Incoming requests can be distributed by a load balancer.  No database or internal network connection is required, although if we found that certain titles were looked up enough we may benefit from a layer of caching, which could be accomplished per-host or with a network cache, depending on the circumstances

One thing that would need more attention if this were to be deployed to the public is security - while the service does not have any special ability to compromise Wikipedia itself given everything is using the public APIs, there is definitely a host of opportunities for malicious action whenever you host code on the internet that accepts user input.  The HTML encoding and decoding I did to the provided titles was for preservation of special characters, so I'm sure there is likely a way to inject and run scripts using the title input.  Similarly, when scaling the hosts, we would want to take care to prevent DDoS-type attacks
