# Chata pri Kaštieli – Rezervačný systém pre WordPress

Tento repozitár obsahuje plne funkčný WordPress plugin `Chata pri Kaštieli Booking`, ktorý poskytuje kompletný rezervačný formulár, kontrolu dostupnosti a e-mailové upozornenia pre prevádzku chaty pri kaštieli.

## Funkcie

- ✨ Elegantný rezervačný formulár so zameraním na používateľa
- 📅 Automatická kontrola dostupnosti prostredníctvom vlastnej REST API trasy
- 📨 E-mailové upozornenia pre administrátora aj hosťa po odoslaní rezervácie
- ⚙️ Nastavenia v administrácii (adresát e-mailov, minimálny počet nocí)
- 📂 Ukladanie rezervácií ako vlastný typ obsahu v administrácii WordPressu

## Inštalácia

1. Stiahnite obsah priečinka `wp-content/plugins/chata-pri-kastieli-booking` do zložky `wp-content/plugins/` vo vašej WordPress inštalácii.
2. V administrácii WordPressu prejdite na **Pluginy → Nainštalované pluginy** a aktivujte **Chata pri Kaštieli Booking**.
3. Po aktivácii sa v menu **Nastavenia → Chata pri Kaštieli** zobrazí stránka s konfiguráciou pluginu.

## Použitie

- Vložte shortcode `[chata_booking_form]` na ľubovoľnú stránku alebo príspevok, kde chcete zobraziť rezervačný formulár.
- (Voliteľne) pridajte shortcode `[chata_availability_message]` nad alebo pod formulár, aby sa zobrazovala správa o dostupnosti v reálnom čase.
- Nové rezervácie sa zobrazia v sekcii **Rezervácie** v administrácii WordPressu.

## Stručný prehľad kódu

- `chata-pri-kastieli-booking.php` – hlavný súbor pluginu, registrácia hookov, načítanie assetov.
- `includes/class-cpk-booking-post-type.php` – definícia vlastného typu obsahu pre rezervácie.
- `includes/class-cpk-settings.php` – administrácia nastavení (e-mail, minimálny pobyt).
- `includes/class-cpk-availability.php` – REST API trasa a logika kontroly dostupnosti.
- `includes/class-cpk-booking-form.php` – spracovanie formulára, ukladanie rezervácií, e-mailové notifikácie.
- `includes/class-cpk-shortcodes.php` – registrácia shortcode-ov a správy pre používateľov.
- `assets/css/booking-form.css` – vzhľad rezervačného formulára.
- `assets/js/booking-form.js` – doplnkové správanie (AJAX kontrola dostupnosti, vylepšené hlášky).

## Požiadavky

- WordPress 5.8 alebo novší
- PHP 7.4 alebo novší

## Podpora a rozšírenie

Plugin je navrhnutý tak, aby sa dal jednoducho rozširovať. V prípade potreby doplnenia ďalších polí, napojenia na externé systémy či prepojenia s platobnou bránou je možné rozšíriť triedy v priečinku `includes/` alebo doplniť nové REST API trasy.

Ak nájdete chybu alebo máte návrh na vylepšenie, vytvorte prosím issue alebo pull request.
