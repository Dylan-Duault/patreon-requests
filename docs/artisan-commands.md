# Artisan Commands Reference

## User Management

### Make User Admin

Grant admin privileges to a user.

```bash
# By email
php artisan user:make-admin user@example.com

# By user ID
php artisan user:make-admin 123

# By Patreon ID
php artisan user:make-admin --patreon-id=12345678
```

**Output:**
```
User user@example.com has been granted admin privileges.
+----+----------+------------------+------------+----------+
| ID | Name     | Email            | Patreon ID | Is Admin |
+----+----------+------------------+------------+----------+
| 1  | John Doe | user@example.com | 12345678   | Yes      |
+----+----------+------------------+------------+----------+
```

### Revoke User Admin

Remove admin privileges from a user.

```bash
# By email
php artisan user:revoke-admin user@example.com

# By user ID
php artisan user:revoke-admin 123
```

**Output:**
```
Admin privileges revoked from user@example.com.
```

## Patreon Integration

### Refresh Memberships

Synchronize Patreon membership status for all users with linked Patreon accounts.

```bash
# Refresh all users
php artisan patreon:refresh-memberships

# Refresh specific user
php artisan patreon:refresh-memberships --user=123
```

**Output:**
```
Refreshing membership status for 15 users...
 15/15 [============================] 100%

 Updated: john@example.com - Status: active_patron, Tier: $10.00
 Updated: jane@example.com - Status: active_patron, Tier: $5.00
 Failed to update: bob@example.com

Completed: 14 updated, 1 failed.
```

**What it does:**
1. Fetches current membership data from Patreon API
2. Updates `patron_status` (active_patron, former_patron, etc.)
3. Updates `patron_tier_cents` (pledge amount)
4. Refreshes OAuth tokens if expired

**When to use:**
- Manually sync a user after pledge changes
- Debug membership issues
- The scheduled task runs this daily automatically

## Scheduled Commands

The following commands run automatically via Laravel's scheduler:

| Command | Schedule | Description |
|---------|----------|-------------|
| `patreon:refresh-memberships` | Daily | Syncs all users' membership status |

To enable scheduled commands, add this to your server's crontab:

```bash
* * * * * cd /path-to-project && php artisan schedule:run >> /dev/null 2>&1
```

## Useful Tinker Commands

Access the Laravel REPL for debugging:

```bash
php artisan tinker
```

### Check User Membership

```php
>>> $user = User::where('email', 'user@example.com')->first()
>>> $user->isActivePatron()
=> true
>>> $user->getMonthlyRequestLimit()
=> 2
>>> $user->getRemainingRequests()
=> 1
```

### View User's Requests

```php
>>> User::find(1)->requests()->get()
```

### Check Pending Queue

```php
>>> VideoRequest::pending()->chronological()->get()
```

### Manually Update User Tier

```php
>>> $user = User::find(1)
>>> $user->update(['patron_status' => 'active_patron', 'patron_tier_cents' => 1000])
```
