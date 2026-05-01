# Starter Flavor — Mittwald Deployment Guide

**Domain-Ziel:** `themeflex.de` (oder ähnlich)  
**Stack:** WordPress + Elementor + Starter Flavor Theme

---

## 1. WordPress auf Mittwald installieren

### Option A: Mittwald Marketplace (einfachste Methode)
1. Mittwald Control Panel öffnen → **Marketplace**
2. "WordPress" wählen → "Installieren"
3. Domain wählen: `themeflex.de`
4. Admin-Daten setzen, fertig.

### Option B: Manuelle Installation via SSH
```bash
# SSH-Verbindung zu Mittwald
ssh USER@HOST.mittwald.de

# WordPress herunterladen
cd /html
wp core download --locale=de_DE

# wp-config erstellen
wp config create \
  --dbname=DEIN_DBNAME \
  --dbuser=DEIN_DBUSER \
  --dbpass=DEIN_DBPASS \
  --dbhost=localhost

# WordPress installieren
wp core install \
  --url="https://themeflex.de" \
  --title="Starter Flavor Demo" \
  --admin_user="admin" \
  --admin_password="SICHERES_PASSWORT" \
  --admin_email="christian@whatsdigital.de"
```

---

## 2. Theme & Plugins installieren

```bash
# Theme hochladen (via SFTP oder WP CLI)
wp theme install starter-flavor-v2.zip --activate

# Elementor installieren
wp plugin install elementor --activate

# Companion Plugin
wp plugin install starter-flavor-addons-v2.zip --activate

# Optional: Weitere Plugins
wp plugin install contact-form-7 --activate
wp plugin install woocommerce --activate
```

**Via SFTP (FileZilla / Cyberduck):**
- Host: `HOST.mittwald.de`
- Port: `22`
- Ziel-Pfad: `/html/wp-content/themes/`
- ZIP-Dateien hochladen, dann in WP Admin aktivieren

---

## 3. Demo importieren

Nach der Aktivierung:
1. WP Admin → **Starter Flavor** → **Demo Importer**
2. Demo auswählen (z.B. "Agency", "Corporate", "Creative")
3. "Import" klicken — importiert Pages, Widgets, Menus, Customizer-Settings
4. Fertig! Die Seite sieht wie die Demo aus.

---

## 4. HTTPS & Domain konfigurieren

```bash
# Let's Encrypt SSL aktivieren (Mittwald macht das automatisch)
# In der Mittwald-Oberfläche:
# Domains → themeflex.de → SSL → Let's Encrypt aktivieren
```

---

## 5. Theme Check ausführen (ThemeForest Requirement)

```bash
# Theme Check Plugin installieren
wp plugin install theme-check --activate

# Im WP Admin:
# Appearance → Theme Check → Theme auswählen → Check
```

---

## 6. Screenshots für ThemeForest erstellen

ThemeForest benötigt:
- **preview.jpg** — 590×300 px (Hauptvorschau)
- **thumbnail.jpg** — 590×590 px (Square thumbnail)

**Empfehlung:** Browser auf Full-HD (1920×1080) setzen, Demo-Startseite öffnen,
dann Screenshot machen und auf die richtigen Maße zuschneiden.

---

## 7. ThemeForest Submission

1. `starter-flavor-v2.zip` vorbereiten (theme folder muss `starter-flavor` heißen)
2. `starter-flavor-addons-v2.zip` als "Required Plugin" vorbereiten
3. Auf **market.envato.com** einloggen
4. **Upload** → WordPress → Elementor
5. Details ausfüllen:
   - Name: "Starter Flavor — 80+ Widget Elementor Theme"
   - Price: $59 (empfohlen)
   - Tags: elementor, multipurpose, agency, portfolio, woocommerce
6. Dateien hochladen, Review abwarten (~1-3 Wochen)

---

## Hilfreiche URLs

- Mittwald Control Panel: https://app.mittwald.de
- Envato Market Upload: https://market.envato.com/envatomarket/upload
- ThemeForest Guidelines: https://help.author.envato.com/hc/en-us/categories/360000052223

---

**Kontakt:** christian@whatsdigital.de
