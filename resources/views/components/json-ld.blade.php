@props(['data'])
{{-- schema.org structured data. `<` is escaped so a string in the data can never close the script tag. --}}
<script type="application/ld+json">{!! json_encode($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP) !!}</script>
