<?php
return array(
    'title' => '//header/h1',
    'test_url' => 'http://opinionator.blogs.nytimes.com/2011/02/03/lost-and-gone-forever/',
    'test_url' => 'http://krugman.blogs.nytimes.com/2012/09/12/a-vote-of-confidence/',
    'test_url' => 'http://bits.blogs.nytimes.com/2012/01/16/wikipedia-plans-to-go-dark-on-wednesday-to-protest-sopa/',
    'body' => array(
         '//div[@class="postContent"]',
    ),
    'strip' => array(
         '//*[@class="shareToolsBox"]',
    ),
);
