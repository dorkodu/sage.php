<?php
  $json = <<<JSON
   {
     "doruk": {
       "typ": "User",
       "atr": ["name", "about", "followerCount"],
       "arg": {
          "id": 123456
       },
     },
     "doruk.organization": {
       "atr": ["name", "website"],
     },
     "doruk.organization.repos": {
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
