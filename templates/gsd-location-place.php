"location": {
    "@type": "Place",
    "name": "<?= $data['venue'] ?>",
    "address": {
        "@type": "PostalAddress",
        "streetAddress": "<?= $data['venue_address'] ?>",
        "addressLocality": "<?= $data['venue_city'] ?>",
        "postalCode": "<?= $data['venue_zip'] ?>",
        "addressRegion": "<?= $data['venue_state'] ?>",
        "addressCountry": "<?= $data['venue_country'] ?>"
    }
},