# Pages Kankirady

11 vues Blade statiques à placer dans resources/views/livewire/.
Elles se rattachent au layout backend fourni précédemment.
Le CSS est intégré à chaque vue et limité à .kt-page.
Aucune variable Blade, requête, génération de données ou action Livewire n’est incluse.
Les données sont des exemples. Les formulaires HTML sont à raccorder à vos méthodes.
Les filtres et exports ne sont pas implémentés.
Les formulaires d’ajout s’ouvrent avec un élément HTML details, sans JavaScript.

Dans chaque classe Livewire, adaptez le nom de la vue :

```php
public function render()
{
    return view('livewire.clients')
        ->layout('components.layouts.backend');
}
```

Exemple de route dans votre groupe auth existant :

```php
Route::get('/clients', \App\Livewire\Clients::class)->name('clients');
```

Créez d’abord les classes correspondantes. Reprenez la même structure pour les autres pages.
Ces vues concernent votre espace Livewire personnalisé, pas le layout natif de Filament.
Le CSS partagé peut être déplacé dans le layout une fois les pages intégrées.
