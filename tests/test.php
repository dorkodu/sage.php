<?php
  $json = <<<JSON
   {
     "berk": {
       "typ": "User",
       "atr": ["name", "about", "followerCount"],
       "arg": {
          "id": 123456
       },
       "lnk": {
         "organization": "berk:organization"
       }
     },
     "berk:organization": {
       "atr": ["name", "website"],
       "lnk": {
         "repos": "berk:organization:repos"
       }
     },
     "berk:organization:repos": {
       "atr": ["title", "description", "starCount"],
       "arg": {
          "filter": "A-Z",
          "reverseOrder": true
       }
     }
   }
  JSON;

  $graphQL = <<<GRAPHQL
  {
    user(id: 123456, filter: "A-Z", reverseOrder: true) {
      name
      about
      followerCount
      organization {
        name
        website
        starCount
        repos(filter: "A-Z", reverseOrder: true) {
          name
          description
          language
          stars
        }
      }
    }
  }
  GRAPHQL;

  class Stopwatch
  {
      protected $beginTimestamp;
      protected $endTimestamp;
      protected $usingMicroseconds;

      public function __construct($useMicroseconds = false)
      {
          $this->usingMicroseconds = $useMicroseconds;
      }

      public function start()
      {
          if ($this->usingMicroseconds === true) {
              $this->beginTimestamp = microtime(true);
          } else {
              $this->beginTimestamp = time();
          }
      }

      public function stop()
      {
          if ($this->usingMicroseconds === true) {
              $this->endTimestamp = microtime(true);
          } else {
              $this->endTimestamp = time();
          }
      }

      public function reset()
      {
          $this->beginTimestamp = 0;
          $this->endTimestamp = 0;
      }

      public function isStopped()
      {
          return (!empty($this->beginTimestamp) && is_numeric($this->beginTimestamp) && !empty($this->endTimestamp) && is_numeric($this->endTimestamp));
      }

      public function isRunning()
      {
          return (!empty($this->beginTimestamp) && is_numeric($this->beginTimestamp));
      }

      public function passedTime()
      {
          if ($this->isStopped()) {
              return $this->endTimestamp - $this->beginTimestamp;
          } elseif ($this->isRunning()) {
              if ($this->usingMicroseconds === true) {
                  return microtime(true) - $this->beginTimestamp;
              } else {
                  return time() - $this->beginTimestamp;
              }
          } else {
              return 0.00;
          }
      }
  }

  $timer = new Stopwatch(true);

  $timer->start();
  $parsedJson = json_decode(json: $json, associative: true);
  $dummy = $parsedJson["berk"];
  printf("%.9f", $timer->passedTime());
  $timer->stop();
