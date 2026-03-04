# Filament Gemini Search

Un plugin Filament v4/v5 qui intègre l'API Google Gemini avec **Google Search Grounding** pour effectuer des recherches web en temps réel directement depuis votre panneau d'administration.

Gemini ne se contente pas de générer du texte — il **effectue de vraies recherches Google**, analyse les résultats, et vous retourne une réponse structurée avec les **sources vérifiables**.

## Fonctionnalités

- Recherche web en temps réel via Gemini + Google Search Grounding
- Page de recherche dédiée avec affichage Markdown des résultats
- Widget compact pour le dashboard
- Sources et liens cliquables vers les sites trouvés
- Historique des recherches en base de données
- Compatible Filament v4 et v5

---

## Obtenir une clé API Gemini

### Étape 1 : Créer un compte Google AI Studio

1. Rendez-vous sur **[Google AI Studio](https://aistudio.google.com/)**
2. Connectez-vous avec votre compte Google

### Étape 2 : Générer une clé API

1. Cliquez sur **"Get API Key"** dans le menu de gauche
2. Cliquez sur **"Create API Key"**
3. Sélectionnez un projet Google Cloud (ou créez-en un)
4. Copiez la clé générée (format : `AIzaSy...`)

### Étape 3 : Plan gratuit vs payant

| | **Plan Gratuit** | **Plan Payant (Pay-as-you-go)** |
|---|---|---|
| **Requêtes/minute** | 15 | 2 000 |
| **Requêtes/jour** | 1 500 | Illimité |
| **Grounding Search** | ✅ Inclus | ✅ Inclus |
| **Coût** | 0 $ | ~0.001-0.01 $ / requête |

> **Conseil** : Le plan gratuit est largement suffisant pour du développement et un usage modéré en production. Pour activer la facturation, rendez-vous sur [Google Cloud Console > Billing](https://console.cloud.google.com/billing).

### Étape 4 : Activer l'API

Si vous rencontrez des erreurs 403, activez l'API manuellement :

1. Allez sur [Google Cloud Console > APIs](https://console.cloud.google.com/apis/library)
2. Cherchez **"Generative Language API"**
3. Cliquez sur **"Activer"**

---

## Installation

```bash
composer require self-project/filament-gemini-search
```

Ajoutez votre clé dans `.env` :

```env
GEMINI_API_KEY=AIzaSy...votre-cle-ici
GEMINI_MODEL=gemini-2.5-flash
```

Publiez la configuration et lancez les migrations :

```bash
php artisan vendor:publish --tag=gemini-search-config
php artisan migrate
```

Enregistrez le plugin dans votre `PanelProvider` :

```php
use SelfProject\FilamentGeminiSearch\FilamentGeminiSearchPlugin;

public function panel(Panel $panel): Panel
{
    return $panel
        // ...
        ->plugin(FilamentGeminiSearchPlugin::make());
}
```

---

## Configuration

```php
// config/gemini-search.php

return [
    'api_key' => env('GEMINI_API_KEY'),
    'model' => env('GEMINI_MODEL', 'gemini-2.5-flash'),
    'grounding' => [
        'enabled' => true, // Active Google Search Grounding
    ],
    'history' => [
        'enabled' => true,  // Sauvegarde les recherches en DB
        'max_records' => 500,
    ],
    'navigation' => [
        'group' => null,         // Groupe de navigation Filament
        'icon' => 'heroicon-o-magnifying-glass',
        'sort' => 100,
    ],
];
```

### Désactiver la page ou le widget

```php
FilamentGeminiSearchPlugin::make()
    ->disablePage()    // Garde uniquement le widget
    ->disableWidget()  // Garde uniquement la page
```

---

## Cas d'utilisation

### 1. Veille tarifaire / Comparaison de prix

Recherchez les prix d'un produit sur différents sites e-commerce. Gemini effectue la recherche Google et retourne les prix avec les liens vers les boutiques.

```
"Prix climatiseur Nasco 2CV 18000 BTU à Abidjan"
```

**Résultat** : Liste des prix en FCFA avec liens vers jumia.ci, eas.ci, jachete.ci, etc.

### 2. Veille concurrentielle

Surveillez ce que font vos concurrents, leurs offres, leurs nouveautés.

```
"Dernières offres et promotions Orange CI forfait internet mars 2026"
```

**Résultat** : Résumé des offres actuelles avec sources officielles.

### 3. Recherche de fournisseurs

Trouvez des fournisseurs pour vos besoins métier directement depuis votre back-office.

```
"Grossistes fournitures de bureau Abidjan avec livraison"
```

**Résultat** : Liste de fournisseurs avec contacts et sites web.

### 4. Support client enrichi

Vos agents support peuvent rechercher des informations produit en temps réel pour répondre aux clients.

```
"Caractéristiques techniques Samsung Galaxy A15 fiche technique complète"
```

**Résultat** : Spécifications détaillées avec sources fiables.

### 5. Recherche réglementaire / juridique

Trouvez des informations sur les réglementations en vigueur.

```
"Procédure création SARL Côte d'Ivoire 2026 documents requis"
```

**Résultat** : Étapes et documents nécessaires avec liens officiels.

### 6. Veille technologique

Restez informé des dernières tendances tech depuis votre dashboard admin.

```
"Nouveautés Laravel 12 principales fonctionnalités"
```

**Résultat** : Résumé des nouveautés avec liens vers la doc officielle.

### 7. Recherche de talents / Recrutement

Recherchez des profils ou des plateformes de recrutement.

```
"Plateformes recrutement développeurs PHP Côte d'Ivoire"
```

### 8. Analyse de marché

Obtenez des données de marché actualisées pour vos décisions business.

```
"Taille du marché e-commerce Afrique de l'Ouest 2025 2026 statistiques"
```

---

## Comment ça marche (sous le capot)

```
┌──────────────┐     ┌──────────────┐     ┌──────────────┐
│  Filament UI │────>│  Gemini API  │────>│ Google Search│
│  (votre app) │     │  (LLM)       │     │ (grounding)  │
└──────────────┘     └──────┬───────┘     └──────────────┘
                            │
                    ┌───────▼───────┐
                    │ Réponse avec  │
                    │ • Texte IA    │
                    │ • Sources web │
                    │ • Requêtes    │
                    └───────────────┘
```

La clé c'est le paramètre `tools: [{ google_search: {} }]` dans l'appel API. Cela dit à Gemini : "Avant de répondre, fais une vraie recherche Google et base ta réponse sur les résultats."

---

## Modèles Gemini disponibles

| Modèle | Vitesse | Qualité | Grounding |
|--------|---------|---------|-----------|
| `gemini-2.5-flash` | Rapide | Très bon | ✅ |
| `gemini-2.5-pro` | Plus lent | Excellent | ✅ |
| `gemini-2.0-flash` | Très rapide | Bon | ✅ |
| `gemini-2.0-flash-lite` | Ultra rapide | Correct | ✅ |

> **Recommandé** : `gemini-2.5-flash` — meilleur rapport vitesse/qualité.

---

## Tests

### Test rapide (sans Laravel)

```bash
curl "https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent" \
  -H "x-goog-api-key: VOTRE_CLE" \
  -H "Content-Type: application/json" \
  -X POST \
  -d '{
    "contents": [{"parts": [{"text": "Prix iPhone 16 Abidjan"}]}],
    "tools": [{"google_search": {}}]
  }'
```

### Test dans Laravel (Tinker)

```bash
php artisan tinker
```

```php
$service = app(\SelfProject\FilamentGeminiSearch\Services\GeminiSearchService::class);
$result = $service->search('Prix climatiseur Nasco 2CV Abidjan');

echo $result->text;        // Réponse formatée
dump($result->sources);    // Sources avec URLs
dump($result->searchQueries); // Requêtes Google effectuées
```

---

## Licence

MIT
