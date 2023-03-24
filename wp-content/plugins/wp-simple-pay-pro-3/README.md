# WP Simple Pay

[![Run PHP CI](https://github.com/awesomemotive/wp-simple-pay-pro/actions/workflows/ci-php.yml/badge.svg)](https://github.com/awesomemotive/wp-simple-pay-pro/actions/workflows/ci-php.yml)

## Development

### PHP

#### Dependencies

PHP dependencies that may be loaded by other plugings (such as [BerlindDB](https://github.com/berlindb/core) and [Container](https://github.com/thephpleague/container)) are managed via [Mozart](https://github.com/coenjacobs/mozart) -- which automatically places the packages under the `\SimplePay\Vendor` namespace in [`./lib`](https://github.com/awesomemotive/wp-simple-pay-pro/tree/master/lib) -- to be included in distribution. Any Mozart-managed package can be referenced by appending `\SimplePay\Vendor` to the package's native namespace. 

*Note*: [Action Scheduler](https://github.com/woocommerce/action-scheduler) is not namespaced and automatically handles loading the latest version of itself when multiple versions are detected. It is installed in [`./lib`](https://github.com/awesomemotive/wp-simple-pay-pro/tree/dev/lib) alongside other dependencies managed by Mozart.

To namespace and move a new dependency:

1. Add the package to [Mozart's package list](https://github.com/awesomemotive/wp-simple-pay-pro/blob/master/composer.json#L57).
2. Run:

```
composer run mozart
```


#### Tests

When a PR is created on GitHub the unit and integration tests are run automatically. To run tests locally:

```
$ composer install
$ bash bin/install-wp-tests.sh <db-name> <db-user> <db-pass> [db-host] [wp-version]
$ composer run test:integration
```

To run legacy tests locally:

Duplicate [`phpunit.dist.xml`](https://github.com/awesomemotive/wp-simple-pay-pro/blob/master/tests/php/legacy/phpunit.xml.dist) to `phpunit.xtml` and add a Stripe API secret key environment variable to the file:

```
<php>
	<env name="SIMPAY_STRIPE_TEST_SECRET_KEY" value="sk_test_123" />
</php>
```

then run the tests:

```
$ composer install
$ bash bin/install-wp-tests.sh <db-name> <db-user> <db-pass> [db-host] [wp-version]
$ composer run test:legacy
```

#### PHPStan

Code added to the [plugin container](https://github.com/awesomemotive/wp-simple-pay-pro/tree/master/src) is analyzed with [PHPStan](https://phpstan.org/) when a PR is created on GithHub. To analyze code locally:

```
$ composer install
$ composer run analzye
```

### JS/CSS

To watch for changes and process JavaScript and CSS:

```
$ npm install
$ npm run dev
```

## Release

1. Bump the version number in [`package.json`](https://github.com/awesomemotive/wp-simple-pay-pro/blob/master/package.json#L5)
2. Bump the version number in the [plugin header](https://github.com/awesomemotive/wp-simple-pay-pro/blob/master/simple-pay.php#L8) and [constant](https://github.com/awesomemotive/wp-simple-pay-pro/blob/master/simple-pay.php#L56)
3. Add changes to [`readme.txt`](https://github.com/awesomemotive/wp-simple-pay-pro/blob/master/readme.txt#L11)
4. Create a build:

```
$ npm install
$ npm run build
```

A `.zip` file will be created in `./build`

