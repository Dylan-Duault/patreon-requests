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

## Credit Management

### Grant Monthly Credits

Grant monthly credits to active patrons based on their tier.

```bash
# Grant to all active patrons
php artisan credits:grant-monthly

# Grant to specific user
php artisan credits:grant-monthly --user=123

# Dry run (preview without making changes)
php artisan credits:grant-monthly --dry-run
```

**Output:**
```
Granting monthly credits to active patrons...
 12/12 [============================] 100%

 Granted: john@example.com - 2 credits ($10.00 tier)
 Granted: jane@example.com - 1 credit ($5.00 tier)
 Skipped: bob@example.com - Already received this month

Completed: 10 granted, 2 skipped.
```

**What it does:**
1. Identifies active patrons who haven't received credits this month
2. Calculates credits based on their tier configuration
3. Creates a `monthly_grant` credit transaction
4. Credits accumulate (not reset) - users keep unused credits

**When to use:**
- Manually grant credits after fixing membership issues
- Test the credit system with `--dry-run`
- The scheduled task runs this on the 1st of each month

## Scheduled Commands

The following commands run automatically via Laravel's scheduler:

| Command | Schedule | Description |
|---------|----------|-------------|
| `patreon:refresh-memberships` | Daily | Syncs all users' membership status |
| `credits:grant-monthly` | 1st of month | Grants monthly credits to active patrons |

To enable scheduled commands, add this to your server's crontab:

```bash
* * * * * cd /path-to-project && php artisan schedule:run >> /dev/null 2>&1
```

## Useful Tinker Commands

Access the Laravel REPL for debugging:

```bash
php artisan tinker
```

### Check User Membership & Credits

```php
>>> $user = User::where('email', 'user@example.com')->first()
>>> $user->isActivePatron()
=> true
>>> $user->getMonthlyRequestLimit()  // Credits earned per month
=> 2
>>> $user->getCreditBalance()  // Total accumulated credits
=> 5
>>> $user->canMakeRequest(2)  // Check if user can afford 2-credit request
=> true
```

### View Credit History

```php
>>> $user->creditTransactions()->get()
>>> $user->creditTransactions()->ofType('monthly_grant')->get()
>>> $user->creditTransactions()->ofType('request')->get()
```

### View User's Requests

```php
>>> User::find(1)->requests()->get()
>>> User::find(1)->requests()->sum('request_cost')  // Total credits spent
```

### Check Pending Queue

```php
>>> VideoRequest::pending()->chronological()->get()
```

### Manually Grant Credits

```php
>>> use App\Models\CreditTransaction;
>>> $user = User::find(1)
>>> CreditTransaction::create([
...     'user_id' => $user->id,
...     'amount' => 5,
...     'type' => 'bonus',
...     'description' => 'Manual bonus'
... ])
```

### Manually Update User Tier

```php
>>> $user = User::find(1)
>>> $user->update(['patron_status' => 'active_patron', 'patron_tier_cents' => 1000])
```
