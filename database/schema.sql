CREATE TABLE `attachments` (
  `id` int NOT NULL AUTO_INCREMENT,
  `file_id` bigint NOT NULL,
  `name` varchar(200) NOT NULL,
  `size` int NOT NULL,
  `json_data` json NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `created_by` int DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `updated_by` int DEFAULT NULL,
  `deleted_at` datetime DEFAULT NULL,
  `deleted_by` int DEFAULT NULL,
  PRIMARY KEY (`id`)
);
