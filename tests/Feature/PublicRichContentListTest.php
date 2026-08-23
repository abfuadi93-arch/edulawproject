<?php

test('public rich content stylesheet displays bullets and numbering', function () {
    $css = file_get_contents(resource_path('css/app.css'));

    expect($css)
        ->toContain('.edulaw-readable :where(ul)')
        ->toContain('list-style-type: disc;')
        ->toContain('.edulaw-readable :where(ol)')
        ->toContain('list-style-type: decimal;')
        ->toContain('padding-inline-start: 1.6rem;')
        ->toContain('.edulaw-readable :where(li)::marker')
        ->toContain('list-style-type: lower-alpha;')
        ->toContain('list-style-type: lower-roman;');
});

test('all public TinyMCE content containers use the readable content class', function () {
    foreach ([
        resource_path('views/insights/show.blade.php'),
        resource_path('views/programs/show.blade.php'),
        resource_path('views/publications/show.blade.php'),
        resource_path('views/opportunities/show.blade.php'),
    ] as $view) {
        expect(file_get_contents($view))->toContain('edulaw-readable');
    }
});
