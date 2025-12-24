# Configuration LiteSpeed Cache pour WP Visitor Stats

## Étapes concrètes à suivre

### 1. Accéder aux réglages LiteSpeed Cache

1. Connectez-vous à **WordPress Admin**
2. Allez dans **LiteSpeed Cache** → **Settings** (ou **Réglages**)
3. Cliquez sur l'onglet **"Excludes"** (ou **"Exclusions"**)

### 2. Exclure les URIs du cache

Dans la section **"Do Not Cache URIs"** (ou **"Ne pas mettre en cache les URIs"**), ajoutez :

```
/wp-admin/admin-ajax.php
```

### 3. Exclure les Query Strings

Dans la section **"Do Not Cache Query Strings"** (ou **"Ne pas mettre en cache les Query Strings"**), ajoutez ces lignes (une par ligne) :

```
wp_visitor_stats_token
action=wp_visitor_stats_ping
action=wp_visitor_stats_get
action=wp_visitor_stats_dashboard_refresh
action=wp_visitor_stats_generate_cookie
```

### 4. Sauvegarder et vider le cache

1. Cliquez sur **"Save Changes"** (ou **"Enregistrer"**)
2. Allez dans **LiteSpeed Cache** → **Toolbox** → **Purge** → **Purge All**
3. Videz le cache complètement

## Alternative : Code dans functions.php

Si vous préférez, ajoutez ce code dans le fichier `functions.php` de votre thème :

```php
// Exclure WP Visitor Stats du cache LiteSpeed
add_filter('litespeed_cache_do_not_cache', function($do_not_cache) {
    if (isset($_GET['wp_visitor_stats_token']) || 
        (isset($_GET['action']) && strpos($_GET['action'], 'wp_visitor_stats') !== false)) {
        return true;
    }
    return $do_not_cache;
}, 10, 1);
```

## Vérification

Après configuration, testez :
1. Visitez votre site
2. Les statistiques devraient se mettre à jour en temps réel
3. Le compteur "Online Now" devrait fonctionner correctement



