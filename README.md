# ESCR Test Distribution

Test Distribution for the Event-Sourced Content Repository

## Packages

* Flow-independant packages:
  * [neos/eventstore](DistributionPackages/neos/event-store) Suggestion for a new Neos package implementing the core Event Store abstraction (Doctrine independant)
  * [neos/eventstore-doctrineadapter](DistributionPackages/neos/event-store-doctrineadapter) Doctrine DBAL based implementation for the neos/event-store (Flow independant)
  * [neos/content-repository](DistributionPackages/neos/content-repository) Mock for the new ESCR PHP Api (implementing simple command bus, blocking command handling, projection abstraction, ...)
  * [neos/content-repository-doctrineadapters](DistributionPackages/neos/content-repository-doctrineadapters) Doctrine DBAL based implementations for the neos/content-repository projections and factories
* Flow packages
  * [Neos.ContentRepositoryRegistry](DistributionPackages/Neos.ContentRepositoryRegistry) Dummy implementation of the "global ESCR registry"
  * [Wwwision.Test](DistributionPackages/Wwwision.Test) Test package, defining two CR instances (`site1` and `site2`) and a simple CommandController to test things via CLI

## Usage

Checkout and install via:

```
git clone https://github.com/bwaidelich/escr-test-distribution.git
cd escr-test-distribution
composer install
```

Afterwards make sure to configure your database connection in `Configuration/Settings.yaml`,
for example:

```yaml
Neos:
  Flow:
    persistence:
      backendOptions:
        driver: pdo_mysql
        dbname: <db>
        user: <user>
        password: <pass>
```

### Setup CR instances

```
./flow cr:setup site1
```

This should create the following 4 database tables:

```
site1_checkpoints
site1_contentgraph
site1_contentstream
site1_events
```

Optionally run `./flow cr:setup site2` to setup another instance.

### Simulate command handling (with blocking)

```
./flow cr:createcontentstream site1 contentstream1
./flow cr:createnode site1 contentstream1 node1
```

The following command will fail (soft constraint check):

```
./flow cr:createnode site1 nonexistingcontentstream some-node
```

The 2nd command will fail (expect version check):

```
./flow cr:createcontentstream cs1
./flow cr:createcontentstream cs1
```

#### Other CR CLI commands

```
  cr:setup                                 Setup EventStore and projections for
                                           the specified site
  cr:reset                                 Reset projection states of the
                                           specified site
  cr:replay                                Replay (i.e. reset and catch up)
                                           projections for the specified site
  cr:catchup                               Catch up projections of the
                                           specified site
  cr:createcontentstream                   Create a content stream in the
                                           specified site
  cr:removecontentstream                   Remove a content stream within the
                                           specified site
  cr:createnode                            Add a node in a content stream of a
                                           site
  cr:getcontentstreams                     List all content streams of the
                                           specified site
```
