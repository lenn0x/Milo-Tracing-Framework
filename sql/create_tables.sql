CREATE TABLE `annotations` (
  `trace_id` bigint(20) NOT NULL,
  `span_id` bigint(20) NOT NULL,
  `name` varchar(64) NOT NULL,
  `annotation_name` varchar(128) NOT NULL,
  `annotation_value` varchar(255) NOT NULL,
  `date_created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  KEY `name` (`name`),
  KEY `key` (`annotation_name`),
  KEY `value` (`annotation_value`),
  FULLTEXT KEY `search_idx` (`name`,`annotation_name`,`annotation_value`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `saved_searches` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `search_name` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `key` varchar(255) NOT NULL,
  `value` varchar(255) NOT NULL,
  `date_created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


CREATE TABLE `traces` (
  `trace_id` bigint(20) NOT NULL,
  `date_created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`trace_id`),
  KEY `date_created` (`date_created`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;