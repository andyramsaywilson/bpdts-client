# bpdts-client
Rest API client for bpdts-test-app.herokuapp.com

# Getting started
## System Requirements
* To run the application locally you'll need docker
* If you have make you can use the build commands https://www.gnu.org/software/make/manual/make.html

* Assuming you have the prequisites, you should be able to run it using the following commands
```
git checkout https://github.com/andyramsaywilson/bpdts-client.git
cd bpdts-client
make up
make test
wget http://localhost:8080/people
make down
```

# Key Features
In this application, I considered two main (non-functional) features
* Performance (by asynchronously calling the upstream APIs)
* Testing (using the 'testing pyramid')

# Performance
* Upstream API requests are made using a Promises.
* The library I used is based on https://promisesaplus.com/

# Testing

Consider the testing pyramid https://martinfowler.com/bliki/TestPyramid.html

## Unit testing
* Low-level components are tested using unit tests (TestCase).
* These have no external dependencies.
* They are fast and reliable.
* Included in code-coverage reports
* Testing pyramid 'Unit'

## Functional testing
* Application logic can be tested end-to-end process, only with mocked API responses. 
* We use the framework's Dependency Injection features, to swap only the component which makes the HTTP requests.
* These tests are still fast and reliable, but are slower than unit tests.
* Included in code-coverage reports.
* Testing pyramid 'Service'

## Mock API server testing
* Application can be fully tested as it will be 'in the wild'
* A basic http server is created, which will simulate the behaviour of the real API
* The API url is stored in a .env file and injected into the application
* Additional components are added to the test application, to allow requests to be made 'over the wire' (e.g. https://chromedriver.chromium.org/)
* These tests are often written in a more human-friendly format, e.g. https://cucumber.io/docs/gherkin/reference/
* These tests are slow
* Not included in code-coverage reports
* Testing pyramid 'UI'

## Real API testing
* Manual testing of the application for a 'sanity test'
* Could be a single manual test as part of a release
* Not listed in testing pyramid, but often included as a manual step

# Notes
## Time-limited
* This was a time-limited excercise, I ran out of time before adding the Mock API server testing steps.
* I would have added more unit tests
* I would have made the functional tests have more assertions, for example asserting that the response payload fields exactly match the expectation based on the response.
* I would have added some performance tests, to prove that the upstream requests are made asynchronously.
* Documentation - I would have liked to have added a sequence diagram - I find plantuml works well in terms of cost / benefit https://plantuml.com/sequence-diagram. For larger projects I would use something like Lucid Chart.
* Language choice - I chose PHP because it's the language with which I'm most familar. If I'd had more time I would built it in Java Spring Boot or Node (both of which I'm actively learning).
* Error logging - the application throws exceptions, each with a unique code and message. I would've added logging to stdout (as per https://12factor.net/logs) via a log component if I'd had more time
* Personal data - this application returns personal data, so any logging needs to be carefully implemented. MY assumption is since the API is public domain, the personal data is not real. In any case, for example, I redacted the names from the test cases, and obfuscated the IP addresses and lat/lng values.