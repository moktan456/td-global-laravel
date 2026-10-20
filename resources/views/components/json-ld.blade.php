@props(['data'])
{{-- schema.org structured data. `<` is escaped so a string in the data can never close the script tag. --}}
<script type="application/ld+json">{!! str_replace('<', '<', json_encode($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)) !!}</script>
