# Project Issues Report

## 1. ErrorPage.php - CSS Syntax Error

**File:** `src/UI/ErrorPage.php`
**Line:** 33

**Issue:** Missing `#` prefix for CSS color value.

```php
// Current (incorrect):
color: 0000;

// Should be:
color: #0000;
```

---

## 2. Route.php - Import Statement Style

**File:** `src/Router/Route.php`
**Lines:** 9-11

**Issue:** While syntactically valid, the import style is inconsistent with a trailing comma before the newline.

```php
// Current:
use ReflectionMethod,

PDO, ReflectionFunction, Exception;

// Recommended:
use ReflectionMethod, PDO, ReflectionFunction, Exception;
```

---

## 3. Route.php - Typo in Variable Name

**File:** `src/Router/Route.php`
**Line:** 173

**Issue:** Variable name typo `$routeKeyalues` should be `$routeKeyValues`.

```php
// Current:
$routeKeyalues = $this->getKeyValues();

// Should be:
$routeKeyValues = $this->getKeyValues();
```

This variable is used on line 177 in the foreach loop.

---

## 4. Route.php - Multiple Typos in Variable Names

**File:** `src/Router/Route.php`
**Lines:** 649-654

**Issue:** Multiple variable name typos - `avaialble` should be `available`.

```php
// Current (lines 649-654):
$avaialbleRoute = array_filter($matches);
unset($avaialbleRoute[0]);
foreach ($avaialbleRoute as $key => $currentRoute) {
    $avaialbleKey = $key;
}
$newServerURL = $parameterRouteKeys[$avaialbleKey - 1];

// Should be:
$availableRoute = array_filter($matches);
unset($availableRoute[0]);
foreach ($availableRoute as $key => $currentRoute) {
    $availableKey = $key;
}
$newServerURL = $parameterRouteKeys[$availableKey - 1];
```

---

## 5. composer.json - Redundant Version Constraint

**File:** `composer.json`
**Line:** 14

**Issue:** The version constraint logic is redundant since `>=7.0` already includes PHP 8.0+.

```json
// Current:
"php": ">=7.0 || >=8.0"

// Should be:
"php": ">=7.0"
```

---

## 6. DependencyInject.php - Interface Assumption

**File:** `src/Container/DependencyInject.php`
**Lines:** 100-107

**Issue:** The `createObjects` method assumes all constructor parameters are interfaces, which will throw exceptions for non-interface type hints.

```php
// The code:
$type = str_replace($interface, '', (string) $parameter->getType());
if (!interface_exists($type . $interface)) {
    throw new Exception($parameter . " Interface is not exist.", 1);
}
```

This will fail for primitive types (string, int) or concrete class type hints.

---

## 7. MethodMiddlewareTrait.php - Unused Variable

**File:** `src/Middleware/MethodMiddlewareTrait.php`
**Line:** 11

**Issue:** Variable declared but never used.

```php
// Current:
$methodNotFound = "404 - Method Not Found";
if (!isset($_REQUEST['__method']) || ... ) {
    return showErrorPage("404 - Method Not Found", 404);
}

// The $methodNotFound variable is never used
```

---

## 8. RouteCache.php - SQL String Interpolation

**File:** `src/Cache/RouteCache.php`
**Line:** 46

**Issue:** Using string concatenation for SQL datetime comparison instead of parameter binding.

```php
// Current:
"SELECT route_method, CASE WHEN expired_time = NULL ...
WHEN expired_time > '" . $currentDateTime . "' THEN ..."

// While $currentDateTime is generated internally, this pattern could be risky
// if the value is manipulated elsewhere.
```

---

## 9. helpers.php - Missing Session Check

**File:** `src/helpers.php`
**Functions:** `generateCSRFToken()`, `csrfToken()`
**Lines:** 13-26

**Issue:** These functions assume a session is already started without checking.

```php
// Current:
function generateCSRFToken() {
    if (empty($_SESSION['csrf_token']) || !isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
}

// Should check if session is started:
function generateCSRFToken() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    if (empty($_SESSION['csrf_token']) || !isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
}
```

---

## 10. RouteCommand.php - Typo in Parameter Name

**File:** `src/Command/RouteCommand.php`
**Lines:** 45, 50, 65, 104, 148, 194, 259, 301

**Issue:** Consistent typo `defaulFolder` instead of `defaultFolder` (missing 't') throughout the file.

```php
// Current (appears in multiple methods):
private function getNamespace(string $defaulFolder)
private function makeControllerContent(string $defaulFolder, string $createdFile)
private function checkContent(string $command, string $defaulFolder, string $createdFile)

// Should be:
private function getNamespace(string $defaultFolder)
private function makeControllerContent(string $defaultFolder, string $createdFile)
private function checkContent(string $command, string $defaultFolder, string $createdFile)
```

---

## 11. RouteCommand.php - Unclosed File Handles (Resource Leak)

**File:** `src/Command/RouteCommand.php`
**Lines:** 280, 307

**Issue:** `fopen()` is called but the file handle is never stored or closed, causing a resource leak.

```php
// Current:
fopen($baseDir . '/' . $this->createdFile . '.php', 'w') or die('Unable to create ' . $createdOption);
file_put_contents($baseDir . '/' . $this->createdFile . '.php', $createdFileContent);

// The fopen() opens the file but never uses or closes the handle.
// file_put_contents() opens the file again.
// Should be:
$fileHandle = fopen($baseDir . '/' . $this->createdFile . '.php', 'w') or die('Unable to create ' . $createdOption);
fclose($fileHandle);
file_put_contents($baseDir . '/' . $this->createdFile . '.php', $createdFileContent);
// Or simply remove the fopen() call since file_put_contents() handles file creation
```

---

## 12. RouteCommand.php - Namespace Generation Bug

**File:** `src/Command/RouteCommand.php`
**Line:** 47

**Issue:** The `ucfirst()` only capitalizes the first character of the entire path, not each folder segment.

```php
// Current:
return str_replace('/', '\\', ucfirst($defaulFolder));

// For input: "app/Middlewares"
// Result: "App/Middlewares" (correct)

// For input: "app/subfolder/Middlewares"
// Result: "App/subfolder/Middlewares" (wrong - 'subfolder' should be 'Subfolder')

// Should be:
return str_replace('/', '\\', implode('\\', array_map('ucfirst', explode('/', $defaulFolder))));
```

---

## Summary Table

| # | File | Line(s) | Severity | Issue |
|---|------|---------|----------|-------|
| 1 | `src/UI/ErrorPage.php` | 33 | Medium | CSS syntax error (missing #) |
| 2 | `src/Router/Route.php` | 9-11 | Low | Import statement style |
| 3 | `src/Router/Route.php` | 173 | Low | Variable typo `$routeKeyalues` |
| 4 | `src/Router/Route.php` | 649-654 | Medium | Variable typos `avaialble` |
| 5 | `composer.json` | 14 | Low | Redundant version constraint |
| 6 | `src/Container/DependencyInject.php` | 100-107 | High | Interface assumption bug |
| 7 | `src/Middleware/MethodMiddlewareTrait.php` | 11 | Low | Unused variable |
| 8 | `src/Cache/RouteCache.php` | 46 | Medium | SQL string interpolation |
| 9 | `src/helpers.php` | 13-26 | Medium | Missing session check |
| 10 | `src/Command/RouteCommand.php` | 45, 50, 65, etc. | Low | Typo `defaulFolder` (missing 't') |
| 11 | `src/Command/RouteCommand.php` | 280, 307 | Medium | Unclosed file handles |
| 12 | `src/Command/RouteCommand.php` | 47 | Medium | Namespace generation bug |

---

## Recommended Priority Order

1. **High Priority:** Fix `DependencyInject.php` interface assumption (could cause runtime errors)
2. **Medium Priority:** Fix CSS error in `ErrorPage.php`, typos in `Route.php`, session handling, RouteCommand file handles
3. **Low Priority:** Clean up unused variables, style inconsistencies, composer.json constraint, RouteCommand typos
