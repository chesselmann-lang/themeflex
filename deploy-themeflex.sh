#!/bin/bash
# ================================================================
# STARTER FLAVOR — Mittwald Deploy Script für themeflex.de
# Ausführen via SSH auf dem Mittwald-Server:
#   bash deploy-themeflex.sh
# ================================================================

set -e

DOMAIN="themeflex.de"
THEME_SLUG="starter-flavor"
WP_PATH="/html"           # Mittwald Standard-Pfad, ggf. anpassen
ADMIN_EMAIL="christian@whatsdigital.de"
ADMIN_USER="admin"
ADMIN_PASS="$(openssl rand -base64 16)"  # Zufälliges sicheres Passwort

BLUE='\033[0;34m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
NC='\033[0m'

log() { echo -e "${BLUE}[SF]${NC} $1"; }
ok()  { echo -e "${GREEN}[OK]${NC} $1"; }
warn(){ echo -e "${YELLOW}[WARN]${NC} $1"; }
err() { echo -e "${RED}[ERR]${NC} $1"; exit 1; }

echo ""
echo "╔══════════════════════════════════════════════════════════╗"
echo "║        STARTER FLAVOR — themeflex.de Deploy             ║"
echo "╚══════════════════════════════════════════════════════════╝"
echo ""

# Prüfen ob WP-CLI verfügbar
command -v wp >/dev/null 2>&1 || {
  log "Installiere WP-CLI..."
  curl -sO https://raw.githubusercontent.com/wp-cli/builds/gh-pages/phar/wp-cli.phar
  chmod +x wp-cli.phar
  mv wp-cli.phar /usr/local/bin/wp
  ok "WP-CLI installiert"
}

cd "$WP_PATH"

# ── 1. WordPress Core ────────────────────────────────────────────
log "Prüfe WordPress Installation..."
if ! wp core is-installed 2>/dev/null; then
  log "Installiere WordPress..."
  wp core install \
    --url="https://${DOMAIN}" \
    --title="ThemeFlex — Premium Elementor Theme" \
    --admin_user="$ADMIN_USER" \
    --admin_password="$ADMIN_PASS" \
    --admin_email="$ADMIN_EMAIL" \
    --skip-email
  ok "WordPress installiert"
  echo ""
  echo "  ⚠️  Admin-Passwort merken: $ADMIN_PASS"
  echo ""
else
  ok "WordPress bereits installiert"
  # URL aktualisieren falls nötig
  wp option update siteurl "https://${DOMAIN}"
  wp option update home "https://${DOMAIN}"
fi

# ── 2. WordPress Grundeinstellungen ─────────────────────────────
log "Konfiguriere WordPress..."
wp option update blogname "ThemeFlex"
wp option update blogdescription "Premium Elementor WordPress Theme"
wp option update permalink_structure "/%postname%/"
wp option update timezone_string "Europe/Berlin"
wp option update date_format "d.m.Y"
wp option update WPLANG "de_DE"
wp rewrite flush
ok "WordPress konfiguriert"

# ── 3. Starter Flavor Theme ──────────────────────────────────────
log "Installiere Starter Flavor Theme..."
THEME_DIR="wp-content/themes/${THEME_SLUG}"

if [ ! -d "$THEME_DIR" ]; then
  # Theme per SFTP hochladen oder direkt installieren
  warn "Theme-Verzeichnis nicht gefunden: $THEME_DIR"
  warn "Bitte Theme per SFTP hochladen oder ZIP-URL angeben:"
  warn "  wp theme install https://DEINE_URL/starter-flavor-v2.zip --activate"
else
  wp theme activate "$THEME_SLUG"
  ok "Theme aktiviert: $THEME_SLUG"
fi

# ── 4. Plugins ───────────────────────────────────────────────────
log "Installiere Plugins..."

# Elementor
wp plugin install elementor --activate
ok "Elementor installiert"

# Starter Flavor Addons
ADDONS_DIR="wp-content/plugins/starter-flavor-addons"
if [ -d "$ADDONS_DIR" ]; then
  wp plugin activate starter-flavor-addons
  ok "Starter Flavor Addons aktiviert"
else
  warn "Starter Flavor Addons nicht gefunden — bitte manuell hochladen"
fi

# Nützliche weitere Plugins
wp plugin install contact-form-7 --activate
wp plugin install wordpress-seo --activate        # Yoast SEO
wp plugin install smush --activate                # Bildoptimierung
ok "Basis-Plugins installiert"

# ── 5. Demo-Content importieren ──────────────────────────────────
log "Importiere Demo-Content..."

# WordPress Importer Plugin
wp plugin install wordpress-importer --activate

# Demo XML importieren (wenn vorhanden)
DEMO_XML="wp-content/themes/${THEME_SLUG}/demo-content/agency/demo-content.xml"
if [ -f "$DEMO_XML" ]; then
  wp import "$DEMO_XML" --authors=create
  ok "Demo-Content importiert"
else
  # Erstelle Basis-Seiten
  log "Erstelle Basis-Seiten..."
  HOME_ID=$(wp post create \
    --post_type=page \
    --post_status=publish \
    --post_title="Home" \
    --post_name="home" \
    --format=ids)
  wp option update page_on_front "$HOME_ID"
  wp option update show_on_front "page"

  wp post create --post_type=page --post_status=publish --post_title="Services" --post_name="services"
  wp post create --post_type=page --post_status=publish --post_title="Portfolio" --post_name="portfolio"
  wp post create --post_type=page --post_status=publish --post_title="About" --post_name="about"
  CONTACT_ID=$(wp post create --post_type=page --post_status=publish --post_title="Contact" --post_name="contact" --format=ids)
  ok "Basis-Seiten erstellt"
fi

# ── 6. Navigation / Menü ─────────────────────────────────────────
log "Erstelle Navigation..."
MENU_ID=$(wp menu create "Main Menu" --format=ids 2>/dev/null || wp menu list --format=ids | head -1)
wp menu location assign "$MENU_ID" main-menu 2>/dev/null || true

# Menüpunkte aus Seiten
for SLUG in home services portfolio about contact; do
  PAGE_ID=$(wp post list --post_type=page --post_status=publish --name="$SLUG" --format=ids 2>/dev/null)
  if [ -n "$PAGE_ID" ]; then
    wp menu item add-post "$MENU_ID" "$PAGE_ID" 2>/dev/null || true
  fi
done
ok "Navigation erstellt"

# ── 7. Theme-Optionen setzen ─────────────────────────────────────
log "Konfiguriere Theme-Optionen..."
wp option update sf_active_skin "agency"
wp option update sf_primary_color "#6C63FF"
wp option update sf_header_layout "default"
wp option update sf_footer_layout "default"
wp option update sf_enable_dark_mode "1"
wp option update sf_logo_width "160"
wp option update sf_preloader "1"
ok "Theme-Optionen gesetzt"

# ── 8. Elementor Einstellungen ───────────────────────────────────
log "Konfiguriere Elementor..."
wp option update elementor_disable_color_schemes "yes"
wp option update elementor_disable_typography_schemes "yes"
wp option update elementor_experiment-e_dom_optimization "active"
wp option update elementor_experiment-e_font_icon_svg "active"
wp option update elementor_global_image_lightbox ""
# Elementor Kit mit Theme-Farben syncen
wp option patch update elementor_active_kit_id 2>/dev/null || true
ok "Elementor konfiguriert"

# ── 9. Performance & SEO ─────────────────────────────────────────
log "Konfiguriere SEO & Performance..."
wp option update wpseo_titles '{"title-home-wpseo":"ThemeFlex — Premium Elementor WordPress Theme","metadesc-home-wpseo":"ThemeFlex ist das mächtigste Elementor WordPress Theme mit 80+ Widgets, 27 Demo-Designs und 20 Skins."}' 2>/dev/null || true
ok "SEO konfiguriert"

# ── 10. HTTPS erzwingen ──────────────────────────────────────────
log "Aktiviere HTTPS..."
wp option update siteurl "https://${DOMAIN}"
wp option update home "https://${DOMAIN}"
wp rewrite flush
ok "HTTPS aktiviert"

# ── 11. Säubern ──────────────────────────────────────────────────
log "Bereinige Installation..."
wp post delete 1 2>/dev/null || true   # Sample Page löschen
wp post delete 2 2>/dev/null || true   # Sample Post löschen
wp comment delete 1 2>/dev/null || true
wp plugin deactivate hello-dolly akismet 2>/dev/null || true
wp plugin delete hello-dolly akismet 2>/dev/null || true
wp cache flush
ok "Bereinigt"

# ── FERTIG ───────────────────────────────────────────────────────
echo ""
echo "╔══════════════════════════════════════════════════════════╗"
echo "║                   ✅ DEPLOY ABGESCHLOSSEN                ║"
echo "╚══════════════════════════════════════════════════════════╝"
echo ""
echo "  🌐 Website:      https://${DOMAIN}"
echo "  🔐 Admin:        https://${DOMAIN}/wp-admin"
echo "  👤 Benutzername: ${ADMIN_USER}"
echo "  🔑 Passwort:     ${ADMIN_PASS}"
echo ""
echo "  📋 Nächste Schritte:"
echo "  1. Theme per SFTP hochladen falls noch nicht geschehen"
echo "  2. Elementor Editor öffnen und Seiten gestalten"
echo "  3. Logo unter Customizer → Site Identity hochladen"
echo "  4. Screenshots für ThemeForest erstellen"
echo ""
