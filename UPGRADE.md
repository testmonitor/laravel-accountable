# Upgrade Guide

## Upgrading To 11.0 From 10.x

### PHP & Laravel Requirements

Accountable 11.0 requires PHP 8.4+ and Laravel 12.8+ (including Laravel 13.0). Support for anything older has been dropped.

### Removed `AccountableSettings`

The `AccountableSettings` class has been removed. `accountable()` now returns `Accountable` instead, which keeps the exact same methods for impersonation, enabling/disabling, and the anonymous user — no changes needed for these:

```php
accountable()->actingAs($user);
accountable()->whileActingAs($user, $callback);
accountable()->reset();
accountable()->enable();
accountable()->disable();
accountable()->setAnonymousUser(['name' => 'Anonymous']);
```

What did change is where you *read* the current state from — `enabled()`, `disabled()`, and `anonymousUser()` moved off `accountable()` and onto static methods on `Accountable`:

Before:
```php
if (accountable()->enabled()) { ... }
if (accountable()->disabled()) { ... }

$fallback = accountable()->anonymousUser();
```

After:
```php
use TestMonitor\Accountable\Accountable;

if (Accountable::enabled()) { ... }
if (Accountable::disabled()) { ... }

$fallback = Accountable::anonymousUser();
```

`accountable()->become($user)` (an undocumented alias for `actingAs()`) has also been removed — use `actingAs()`.

The column-name accessors moved too:

Before:
```php
accountable()->createdByColumn();
accountable()->updatedByColumn();
accountable()->deletedByColumn();
```

After:
```php
use TestMonitor\Accountable\AccountableColumns;

AccountableColumns::createdByColumn();
AccountableColumns::updatedByColumn();
AccountableColumns::deletedByColumn();
```

### Migration Helper Moved & Renamed

Before:
```php
use TestMonitor\Accountable\Accountable;

Accountable::columns($table);
Accountable::columns($table, false); // without soft-deletes column
```

After:
```php
use TestMonitor\Accountable\AccountableColumns;

AccountableColumns::add($table);
AccountableColumns::add($table, usesSoftDeletes: false);
```

Note: new columns are now created with `foreignId()` (unsigned big integer) rather than `unsignedInteger()`, matching modern Laravel's default. This only affects migrations written *after* upgrading — existing tables/columns are untouched unless you write a migration to alter them.

### Removed Deprecated Methods

The `createdBy()`, `updatedBy()`, and `deletedBy()` methods (deprecated since 8.0.0) have been removed in favor of `creator()`, `editor()`, and `deleter()`.

Before:
```php
$model->createdBy;
$model->updatedBy;
$model->deletedBy;
```

After:
```php
$model->creator;
$model->editor;
$model->deleter;
```
