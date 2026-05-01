# ThemeFlex v4.0.0 — ThemeForest Submission Checklist

**Datum:** 2026-05-01  
**Status:** Bereit zur Einreichung (alle Sandbox-Schritte abgeschlossen)

---

## 1. LOKALE SCHRITTE (Christian — lokal ausführen)

### 1a. Deploy auf themeflex.de (Live-Demo)

```bash
cd /pfad/zu/entwicklung/
node tf-deploy-v13.js
```

Der Script nutzt `~/.ssh/mittwald_key` (ED25519) und deployt auf:
`ssh.altgemeinde.project.host → /home/p-uydwb7/html/wordpress/wp-content/themes/themeflex/`

**Nach dem Deploy — WP Admin prüfen:**
- [ ] https://themeflex.de lädt ohne Fehler
- [ ] WP Admin → Appearance → Themes → ThemeFlex → Version: 4.0.0 sichtbar
- [ ] WP Admin → ThemeFlex → AI Settings → zeigt leeren Zustand (kein Key = kein Fehler)
- [ ] WP Admin → ThemeFlex → Legal Wizard → erreichbar
- [ ] WP Admin → ThemeFlex → AVV Generator → erreichbar
- [ ] WP Admin → ThemeFlex → Setup Wizard → erreichbar
- [ ] debug.log auf Server: 0 Zeilen (`tail /home/p-uydwb7/html/wordpress/wp-content/debug.log`)

### 1b. Git + GitHub

```bash
cd /pfad/zu/entwicklung/
bash tf-git-init.sh
```

Erfordert `gh` CLI authentifiziert, oder manuell:
```bash
cd themeflex/
git remote add origin git@github.com:whatsdigital/themeflex.git
git push -u origin main
```

---

## 2. THEMEFOREST ACCOUNT VORBEREITUNG

- [ ] ThemeForest Author-Account unter https://themeforest.net/dashboard öffnen
- [ ] "Upload" oder "Add New Item" klicken
- [ ] Kategorie: **WordPress → WordPress Themes**
- [ ] Unterkategorie: **Corporate / Business**
- [ ] Tags: `dark theme, ai, dsgvo, gdpr, elementor, responsive, one page, portfolio, landing page`

---

## 3. UPLOAD-DATEIEN (alle in `mnt/wp-plugins/`)

| Datei | Zweck | Status |
|-------|-------|--------|
| `themeflex-v4.0.0.zip` | **Main File** (Theme-ZIP) | ✅ Bereit |
| `item-description-v4.0.0.html` | Item Description (in TF-Editor einfügen) | ✅ Bereit |
| `THEMEFOREST-SUBMISSION-v4.0.0.md` | Diese Checkliste | ✅ Bereit |

**ThemeForest erwartet:**
- Main File: `themeflex-v4.0.0.zip` — direkt hochladen
- Item Name: `ThemeFlex — Premium Dark-First WordPress Theme`
- Item Description: Inhalt aus `item-description-v4.0.0.html` (HTML in TF-Editor einfügen)

---

## 4. ITEM-FORMULAR AUSFÜLLEN

### Pflichtfelder

```
Item Name:        ThemeFlex — Premium Dark-First WordPress Theme
Item Category:    WordPress Themes → Corporate
Tags:             dark theme, ai chat, dsgvo, gdpr, elementor, responsive, portfolio, 
                  landing page, business, one page
Compatible with:  WordPress 5.8+, PHP 8.0+
High Resolution:  Yes
Widget Ready:     Yes
Compatible Browsers: Chrome, Firefox, Safari, Edge (IE11: No)
```

### Preisempfehlung

| Lizenz | Preis |
|--------|-------|
| Regular License | **$69** |
| Extended License | Standard TF-Pricing |

Begründung: Direktkonkurrenz Avada ($69), Divi ($89/Jahr). ThemeFlex hat durch AI Chat + DSGVO Stack einen klaren Mehrwert gegenüber vergleichbaren $29-$49 Themes.

---

## 5. SCREENSHOTS (Christian muss Screenshots machen)

ThemeForest erwartet folgende Preview-Bilder:

| Datei | Auflösung | Inhalt |
|-------|-----------|--------|
| `preview.jpg` | 590 × 300 px | Haupt-Thumbnail (Collage der 3–4 besten Templates) |
| `screenshot.png` | 1200 × 900 px | Vollbild-Screenshot Homepage-Demo |
| Optional: weitere | beliebig | Feature-Highlights (AI Chat Widget, Wizard, etc.) |

**Empfehlung für Screenshots:**
1. Browser auf themeflex.de öffnen (nach Deploy)
2. Agency-Template oder SaaS-Template als Haupt-Demo
3. Screenshot des AI Chat Widgets (klein, floating bubble)
4. Screenshot des Setup Wizards (Admin)

---

## 6. LIVE-DEMO KONFIGURATION

ThemeForest-Reviewer besuchen die Live-Demo. Empfehlung für themeflex.de:

- [ ] Startseite: Beste Template-Demo aktiv (z.B. Agency oder SaaS)
- [ ] Skin: Indigo (Default) oder Custom
- [ ] Alle 8 Skins per Customizer-Link erreichbar
- [ ] Contact Form sichtbar (mit Math-Captcha)
- [ ] AI Chat Widget deaktiviert (kein API Key hinterlegen — Reviewer sollen Consent-Gate sehen, nicht einen leeren Chat)
- [ ] WP Admin Passwort für Reviewer: eigene Demo-Credentials anlegen (nie Produktions-Admin-Passwort)

---

## 7. README.txt PRÜFUNG

```bash
head -30 /path/to/themeflex/README.txt
```

Soll enthalten:
- `Stable tag: 4.0.0`
- PHP 8.0+ Anforderung
- AI Chat als Feature
- DSGVO Stack als Feature

**Status: ✅ README.txt ist auf v4.0.0 aktualisiert**

---

## 8. STYLE.CSS PRÜFUNG

```
Theme Name:   ThemeFlex
Version:      4.0.0
```

**Status: ✅ style.css Version 4.0.0**

---

## 9. THEMEFOREST QUALITY CHECKLIST (Reviewer-Kriterien)

### Code-Qualität
- [x] PHP 8.0+ kompatibel (null-safe operators, match, typed props)
- [x] ABSPATH-Guard in allen PHP-Dateien: `defined('ABSPATH') || exit;`
- [x] Alle AJAX-Handler: `check_ajax_referer()` + `current_user_can()` (nopriv-Handler korrekt)
- [x] Alle Form-Inputs: `sanitize_text_field()` / `sanitize_textarea_field()` + `wp_unslash()`
- [x] Alle Ausgaben: `esc_html()` / `esc_attr()` / `esc_url()`
- [x] Brace-Balance: alle 19 v4.0-PHP-Dateien = OK
- [x] Alle Klassen laden via `file_exists()` Guard in functions.php

### WordPress Standards
- [x] `get_header()` + `get_footer()` in allen 114 Page-Templates
- [x] Kein doppeltes DOCTYPE/HTML in Templates
- [x] `wp_nonce_field()` in allen Admin-Formularen
- [x] Alle Strings in `__()/esc_html_e()` für i18n

### Assets
- [x] Vanilla JS — kein jQuery auf Front-End
- [x] CSS Custom Properties als Design-Token-System
- [x] Google Fonts werden automatisch lokal gehostet (DSGVO)
- [x] Lazy-loading auf Bilder

### Accessibility
- [x] WCAG 2.1 AA — ARIA Labels, Skip-Links, Focus-Management
- [x] Keyboard-Navigation in Accordions und Tabs
- [x] Ausreichende Kontrastverhältnisse in allen 8 Skins

### Security
- [x] Math-Captcha auf allen Contact-Forms (transient-basiert, single-use)
- [x] Honeypot-Felder auf allen Formularen
- [x] `wp_safe_redirect()` nach Admin-Form-Submissions
- [x] FPDF-Output: Force-Download (keine inline PDF-Anzeige)

---

## 10. NACH DER EINREICHUNG

ThemeForest-Review dauert typisch **5–10 Werktage**.

**Häufige Ablehnungsgründe und wie ThemeFlex sie vermeidet:**
- ~~Fehlende `get_footer()`~~ → Alle 114 Templates geprüft ✅
- ~~Doppeltes DOCTYPE~~ → Alle 53 Hybrid-Templates bereinigt ✅
- ~~Fehlende Escaping~~ → Vollständig implementiert ✅
- ~~GPL-inkompatible Bibliotheken~~ → FPDF 1.86 ist MIT-lizenziert ✅
- ~~Demo-Inhalt mit Copyright-Verletzungen~~ → Alle Inhalte sind generiert/placeholder ✅

**Bei Ablehnung:**
- Review-Feedback genau lesen
- Gib in der Antwort an: "Diese Issues wurden in v4.0.1 behoben" und re-submit

---

## ZUSAMMENFASSUNG

```
Submission-Paket:    themeflex-v4.0.0.zip (3.6 MB, 1289 Dateien)
Item Description:    item-description-v4.0.0.html
Empfohlener Preis:   $69 Regular License
Live-Demo:           themeflex.de (nach Deploy)
GitHub (privat):     github.com/whatsdigital/themeflex (nach tf-git-init.sh)
```

**Noch ausstehend (lokal):**
1. `node tf-deploy-v13.js` — Deploy auf themeflex.de
2. `bash tf-git-init.sh` — GitHub Push
3. Screenshots für TF-Einreichung machen
4. ThemeForest Upload-Formular ausfüllen
