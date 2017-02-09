require.config({
    paths: {
        'bootstrap': 'lib/bootstrap.min',
        'modernizr': 'lib/modernizr.custom',
        'jquery.shuffle': 'lib/jquery.shuffle_3.1.1',
//        'jquery.shuffle': 'lib/jquery.shuffle.min',
        'jquery.autosize': 'lib/jquery.autosize.min',
        'jquery-ui': 'lib/jquery-ui-1.10.4.custom.min',
        'jquery.searchbox': 'lib/searchbox'
    },
    shim: {
        'bootstrap': {
            deps: ['jquery-loader']
        },
        'modernizr': {
            exports: 'Modernizr'
        },
        'jquery.autosize': ['jquery-loader'],
        'jquery-ui': ['jquery-loader'],
        'jquery.searchbox': ['jquery-loader']
    }
});