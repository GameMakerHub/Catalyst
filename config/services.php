services:
    _defaults:
        autowire: true
        autoconfigure: true
        public: false

    GMDepMan\:
        resource: '../src/*'

    Symfony\Component\Console\Application:
        public: true