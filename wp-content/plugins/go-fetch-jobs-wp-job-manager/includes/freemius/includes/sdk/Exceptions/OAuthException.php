<<<<<<< HEAD
<?php
	if ( ! class_exists( 'Freemius_Exception' ) ) {
		exit;
	}

    class Freemius_OAuthException extends Freemius_Exception
    {
        public function __construct($pResult)
        {
            parent::__construct($pResult);
        }
=======
<?php
	if ( ! class_exists( 'Freemius_Exception' ) ) {
		exit;
	}

    class Freemius_OAuthException extends Freemius_Exception
    {
        public function __construct($pResult)
        {
            parent::__construct($pResult);
        }
>>>>>>> 88f8b5285171c25644c97fdea4dd80ba70b2875c
    }