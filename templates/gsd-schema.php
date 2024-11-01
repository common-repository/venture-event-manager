{
    "@context": "https://schema.org",
    "@type": "Event",
    "name": "<?= $data['name'] ?>",
    "startDate": "<?= $data['start'] ?>",<?= $data['previous'] ?>
    "eventStatus": "https://schema.org/<?= $data['status'] ?>",
    <?= $data['location'] ?>
    <?= $data['image'] ?>
    "description": "<?= $data['excerpt'] ?>",
    "endDate": "<?= $data['end'] ?>"
}
