[![GitHub stars](https://img.shields.io/github/stars/webonyx/graphql-php.svg?style=social&label=Star)](https://github.com/webonyx/graphql-php)
[![Build Status](https://travis-ci.org/webonyx/graphql-php.svg?branch=master)](https://travis-ci.org/webonyx/graphql-php)
[![Coverage Status](https://coveralls.io/repos/github/webonyx/graphql-php/badge.svg)](https://coveralls.io/github/webonyx/graphql-php)
[![Latest Stable Version](https://poser.pugx.org/webonyx/graphql-php/version)](https://packagist.org/packages/webonyx/graphql-php)
[![License](https://poser.pugx.org/webonyx/graphql-php/license)](https://packagist.org/packages/webonyx/graphql-php)

# Documentation — Sage.php

Sage is a simple, modern way to build APIs consumed by the web and mobile clients. It is intended to be an alternative to GraphQL, REST and SOAP.

Sage itself is a [protocol](https://github.com/dorkodu/sage) designed by [Dorkodu](https://dorkodu.com).

Great overview of the features and benefits of Sage is presented on [its website](http://libre.dorkodu.com/sage). 
All of them equally apply to this PHP implementation. 


## About

**sage.php** is a feature-complete implementation of Sage protocol, in PHP. 

This library is a thin middleware on top of your existing data and business logic layers. It doesn't dictate how these layers are implemented or which storage engines are used. Instead, it provides the utility for creating and awesome API for your existing app.

Library features include:

 - Built-in Types to express your app’s data as a [Type System](type-system/index.md)
 - Validation and introspection of this Type System
 - Parsing, validating and [executing Sage queries](executing-queries.md) against this Type System
 - Rich [error reporting](error-handling.md), including query validation and execution errors
 - Tools for [batching requests](data-fetching.md#solving-n1-problem) to backend storage
 - [Async PHP platforms support](data-fetching.md#async-php) via Promises

## Current Status
This library is under development as July 10th 2021.

The goal with the first version is to support all features described by Sage specification as well as some experimental features.

We work really hard and look forward to see Sage.php being ready for real-world usage. 

## GitHub
Project source code is [hosted on GitHub](https://github.com/dorkodu/sage.php).
