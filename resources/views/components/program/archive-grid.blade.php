@props([
    'programs' => collect(),
    'categories' => collect(),
    'years' => collect(),
    'search' => '',
    'selectedCategory' => '',
    'selectedYear' => '',
])

<x-program.archive-slider
    :programs="$programs"
    :categories="$categories"
    :years="$years"
    :search="$search"
    :selected-category="$selectedCategory"
    :selected-year="$selectedYear"
/>
