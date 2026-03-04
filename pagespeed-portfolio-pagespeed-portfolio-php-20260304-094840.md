# Plugin Check Report

**Plugin:** PageSpeed Portfolio
**Generated at:** 2026-03-04 09:48:40


## `includes/class-psp-db.php`

| Line | Column | Type | Code | Message | Docs |
| --- | --- | --- | --- | --- | --- |
| 64 | 9 | WARNING | WordPress.DB.DirectDatabaseQuery.DirectQuery | Use of a direct database call is discouraged. |  |
| 64 | 9 | WARNING | WordPress.DB.DirectDatabaseQuery.NoCaching | Direct database call without caching detected. Consider using wp_cache_get() / wp_cache_set() or wp_cache_delete(). |  |
| 64 | 10 | WARNING | PluginCheck.Security.DirectDB.UnescapedDBParameter | Unescaped parameter $table_name used in $wpdb-&gt;query(&quot;DROP TABLE IF EXISTS {$table_name}&quot;)\n$table_name assigned unsafely at line 63:\n $table_name = self::get_table_name() |  |
| 64 | 23 | WARNING | WordPress.DB.DirectDatabaseQuery.SchemaChange | Attempting a database schema change is discouraged. |  |
| 81 | 16 | WARNING | WordPress.DB.DirectDatabaseQuery.DirectQuery | Use of a direct database call is discouraged. |  |
| 109 | 16 | WARNING | WordPress.DB.DirectDatabaseQuery.DirectQuery | Use of a direct database call is discouraged. |  |
| 109 | 16 | WARNING | WordPress.DB.DirectDatabaseQuery.NoCaching | Direct database call without caching detected. Consider using wp_cache_get() / wp_cache_set() or wp_cache_delete(). |  |
| 109 | 17 | WARNING | PluginCheck.Security.DirectDB.UnescapedDBParameter | Unescaped parameter $table_name used in $wpdb-&gt;get_results($wpdb-&gt;prepare(\n\t\t\t\t&quot;SELECT performance, accessibility, best_practices, seo, recorded_at\n\t\t\t\tFROM {$table_name}\n\t\t\t\tWHERE site_id = %d AND strategy = %s\n\t\t\t\tORDER BY recorded_at DESC\n\t\t\t\tLIMIT %d&quot;, \t\t\t\tabsint( $site_id ),\n\t\t\t\tsanitize_text_field( $strategy ),\n\t\t\t\tabsint( $limit )\n\t\t\t))\n$table_name assigned unsafely at line 107:\n $table_name = self::get_table_name() |  |
| 112 | 1 | WARNING | WordPress.DB.PreparedSQL.InterpolatedNotPrepared | Use placeholders and $wpdb-&gt;prepare(); found interpolated variable {$table_name} at \t\t\t\tFROM {$table_name}\n |  |
| 133 | 16 | WARNING | WordPress.DB.DirectDatabaseQuery.DirectQuery | Use of a direct database call is discouraged. |  |
| 133 | 16 | WARNING | WordPress.DB.DirectDatabaseQuery.NoCaching | Direct database call without caching detected. Consider using wp_cache_get() / wp_cache_set() or wp_cache_delete(). |  |

## `includes/class-psp-cron.php`

| Line | Column | Type | Code | Message | Docs |
| --- | --- | --- | --- | --- | --- |
| 131 | 4 | WARNING | WordPress.PHP.DevelopmentFunctions.error_log_error_log | error_log() found. Debug code should not normally be used in production. |  |

## `uninstall.php`

| Line | Column | Type | Code | Message | Docs |
| --- | --- | --- | --- | --- | --- |
| 30 | 13 | WARNING | WordPress.DB.DirectDatabaseQuery.DirectQuery | Use of a direct database call is discouraged. |  |
| 30 | 13 | WARNING | WordPress.DB.DirectDatabaseQuery.NoCaching | Direct database call without caching detected. Consider using wp_cache_get() / wp_cache_set() or wp_cache_delete(). |  |
| 41 | 1 | WARNING | WordPress.DB.DirectDatabaseQuery.DirectQuery | Use of a direct database call is discouraged. |  |
| 41 | 1 | WARNING | WordPress.DB.DirectDatabaseQuery.NoCaching | Direct database call without caching detected. Consider using wp_cache_get() / wp_cache_set() or wp_cache_delete(). |  |
| 47 | 1 | WARNING | WordPress.DB.DirectDatabaseQuery.DirectQuery | Use of a direct database call is discouraged. |  |
| 47 | 1 | WARNING | WordPress.DB.DirectDatabaseQuery.NoCaching | Direct database call without caching detected. Consider using wp_cache_get() / wp_cache_set() or wp_cache_delete(). |  |
| 47 | 8 | WARNING | PluginCheck.Security.DirectDB.UnescapedDBParameter | Unescaped parameter $table_name used in $wpdb-&gt;query(&quot;DROP TABLE IF EXISTS {$table_name}&quot;)\n$table_name assigned unsafely at line 46:\n $table_name = $wpdb-&gt;prefix . &#039;psp_scores_history&#039; |  |
| 47 | 15 | WARNING | WordPress.DB.DirectDatabaseQuery.SchemaChange | Attempting a database schema change is discouraged. |  |
| 50 | 1 | WARNING | WordPress.DB.DirectDatabaseQuery.DirectQuery | Use of a direct database call is discouraged. |  |
| 50 | 1 | WARNING | WordPress.DB.DirectDatabaseQuery.NoCaching | Direct database call without caching detected. Consider using wp_cache_get() / wp_cache_set() or wp_cache_delete(). |  |
