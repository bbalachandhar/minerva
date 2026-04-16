-- MariaDB dump 10.19  Distrib 10.4.28-MariaDB, for osx10.10 (x86_64)
--
-- Host: localhost    Database: mcekknagar
-- ------------------------------------------------------
-- Server version	10.4.28-MariaDB

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `addon_versions`
--

DROP TABLE IF EXISTS `addon_versions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `addon_versions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `addon_id` int(11) DEFAULT NULL,
  `version` varchar(10) DEFAULT NULL,
  `version_order` int(11) DEFAULT NULL,
  `folder_path` text DEFAULT NULL,
  `sort_description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `addon_id` (`addon_id`),
  CONSTRAINT `addon_versions_ibfk_1` FOREIGN KEY (`addon_id`) REFERENCES `addons` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `addons`
--

DROP TABLE IF EXISTS `addons`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `addons` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `product_id` int(11) NOT NULL,
  `image` text NOT NULL,
  `name` varchar(500) DEFAULT NULL,
  `config_name` varchar(200) NOT NULL DEFAULT '',
  `short_name` varchar(100) NOT NULL,
  `directory` varchar(500) NOT NULL,
  `description` text DEFAULT NULL,
  `price` float(10,2) NOT NULL DEFAULT 0.00,
  `current_version` varchar(50) DEFAULT NULL,
  `article_link` text NOT NULL,
  `installation_by` int(11) DEFAULT NULL,
  `uninstall_version` varchar(50) DEFAULT NULL,
  `unistall_by` int(11) DEFAULT NULL,
  `addon_prod` text DEFAULT NULL,
  `addon_ver` text DEFAULT NULL,
  `last_update` datetime DEFAULT NULL,
  `current_stage` int(11) NOT NULL DEFAULT 0 COMMENT '0 for buy addon,1 for folder available ready to install,2 for folder addon installed',
  `product_order` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `alumni_events`
--

DROP TABLE IF EXISTS `alumni_events`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `alumni_events` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` text NOT NULL,
  `event_for` varchar(100) NOT NULL,
  `session_id` int(11) DEFAULT NULL,
  `class_id` int(11) DEFAULT NULL,
  `section` varchar(255) NOT NULL,
  `from_date` datetime NOT NULL,
  `to_date` datetime NOT NULL,
  `note` text NOT NULL,
  `photo` varchar(255) DEFAULT NULL,
  `is_active` int(11) NOT NULL,
  `event_notification_message` text NOT NULL,
  `show_onwebsite` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `session_id` (`session_id`),
  KEY `class_id` (`class_id`),
  CONSTRAINT `alumni_events_ibfk_1` FOREIGN KEY (`session_id`) REFERENCES `sessions` (`id`) ON DELETE CASCADE,
  CONSTRAINT `alumni_events_ibfk_2` FOREIGN KEY (`class_id`) REFERENCES `classes` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `alumni_students`
--

DROP TABLE IF EXISTS `alumni_students`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `alumni_students` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `student_id` int(11) NOT NULL,
  `current_email` varchar(255) NOT NULL,
  `current_phone` varchar(255) NOT NULL,
  `occupation` text NOT NULL,
  `address` text NOT NULL,
  `photo` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `student_id` (`student_id`),
  CONSTRAINT `alumni_students_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `annual_calendar`
--

DROP TABLE IF EXISTS `annual_calendar`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `annual_calendar` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `session_id` int(11) DEFAULT NULL,
  `holiday_type` int(11) NOT NULL,
  `from_date` datetime DEFAULT NULL,
  `to_date` datetime DEFAULT NULL,
  `description` text NOT NULL,
  `is_active` int(11) NOT NULL DEFAULT 1,
  `holiday_color` varchar(200) NOT NULL,
  `front_site` int(11) NOT NULL DEFAULT 0,
  `created_by` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `session_id` (`session_id`),
  CONSTRAINT `annual_calendar_ibfk_1` FOREIGN KEY (`session_id`) REFERENCES `sessions` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=59 DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `api_session`
--

DROP TABLE IF EXISTS `api_session`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `api_session` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `params` text NOT NULL,
  `createdat` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `attendence_type`
--

DROP TABLE IF EXISTS `attendence_type`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `attendence_type` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `type` varchar(50) DEFAULT NULL,
  `key_value` varchar(50) NOT NULL,
  `long_lang_name` varchar(250) DEFAULT NULL,
  `long_name_style` varchar(250) DEFAULT NULL,
  `is_active` varchar(255) DEFAULT 'no',
  `for_qr_attendance` int(11) NOT NULL DEFAULT 1,
  `for_schedule` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `biometric_devices`
--

DROP TABLE IF EXISTS `biometric_devices`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `biometric_devices` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `device_name` varchar(255) NOT NULL,
  `serial_number` varchar(255) NOT NULL,
  `api_endpoint` varchar(255) NOT NULL,
  `username` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `brand` varchar(100) NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `book_issues`
--

DROP TABLE IF EXISTS `book_issues`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `book_issues` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `book_id` int(11) NOT NULL,
  `member_id` int(11) DEFAULT NULL,
  `duereturn_date` date DEFAULT NULL,
  `return_date` date DEFAULT NULL,
  `issue_date` date DEFAULT NULL,
  `is_returned` int(11) DEFAULT 0,
  `is_active` varchar(10) NOT NULL DEFAULT 'no',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `book_id` (`book_id`),
  KEY `member_id` (`member_id`),
  CONSTRAINT `book_issues_ibfk_1` FOREIGN KEY (`book_id`) REFERENCES `books` (`id`) ON DELETE CASCADE,
  CONSTRAINT `book_issues_ibfk_2` FOREIGN KEY (`member_id`) REFERENCES `libarary_members` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=141 DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `books`
--

DROP TABLE IF EXISTS `books`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `books` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `book_title` varchar(100) NOT NULL,
  `book_no` varchar(50) NOT NULL,
  `barcode` varchar(255) DEFAULT NULL,
  `category_name` varchar(255) DEFAULT NULL,
  `subcategory_name` varchar(255) DEFAULT NULL,
  `isbn_no` varchar(100) NOT NULL,
  `subject` varchar(100) DEFAULT NULL,
  `rack_no` varchar(100) NOT NULL,
  `shelf_id` varchar(255) DEFAULT NULL,
  `class_no` varchar(255) DEFAULT NULL,
  `edition_type` varchar(255) DEFAULT NULL,
  `publish_year` int(11) DEFAULT NULL,
  `purchase_date` date DEFAULT NULL,
  `bill_no` varchar(255) DEFAULT NULL,
  `bill_date` date DEFAULT NULL,
  `pages` int(11) DEFAULT NULL,
  `department` varchar(255) DEFAULT NULL,
  `publish` varchar(100) DEFAULT NULL,
  `edition` varchar(255) DEFAULT NULL,
  `medium` varchar(255) DEFAULT NULL,
  `book_type` varchar(255) DEFAULT NULL,
  `author` varchar(100) DEFAULT NULL,
  `author2` varchar(255) DEFAULT NULL,
  `qty` int(11) DEFAULT NULL,
  `perunitcost` float(10,2) DEFAULT NULL,
  `postdate` date DEFAULT NULL,
  `description` text DEFAULT NULL,
  `available` varchar(10) DEFAULT 'yes',
  `is_active` varchar(255) DEFAULT 'no',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `vendor` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=33607 DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `captcha`
--

DROP TABLE IF EXISTS `captcha`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `captcha` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(250) NOT NULL,
  `status` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `categories`
--

DROP TABLE IF EXISTS `categories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `categories` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `category` varchar(100) DEFAULT NULL,
  `is_active` varchar(255) DEFAULT 'no',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=22 DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `cbse_category`
--

DROP TABLE IF EXISTS `cbse_category`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cbse_category` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `cbse_exam_assessment_types`
--

DROP TABLE IF EXISTS `cbse_exam_assessment_types`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cbse_exam_assessment_types` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `cbse_exam_assessment_id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `code` varchar(100) NOT NULL,
  `maximum_marks` float NOT NULL,
  `pass_percentage` float NOT NULL,
  `description` mediumtext NOT NULL,
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `cbse_exam_assessment_id` (`cbse_exam_assessment_id`),
  KEY `idx_name` (`name`),
  KEY `idx_code` (`code`),
  CONSTRAINT `cbse_exam_assessment_types_ibfk_1` FOREIGN KEY (`cbse_exam_assessment_id`) REFERENCES `cbse_exam_assessments` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `cbse_exam_assessments`
--

DROP TABLE IF EXISTS `cbse_exam_assessments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cbse_exam_assessments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `description` mediumtext NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `cbse_exam_class_sections`
--

DROP TABLE IF EXISTS `cbse_exam_class_sections`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cbse_exam_class_sections` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `cbse_exam_id` int(11) NOT NULL,
  `class_section_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `class_section_id` (`class_section_id`),
  KEY `cbse_exam_id` (`cbse_exam_id`),
  CONSTRAINT `cbse_exam_class_sections_ibfk_1` FOREIGN KEY (`class_section_id`) REFERENCES `class_sections` (`id`) ON DELETE CASCADE,
  CONSTRAINT `cbse_exam_class_sections_ibfk_2` FOREIGN KEY (`cbse_exam_id`) REFERENCES `cbse_exams` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `cbse_exam_grades`
--

DROP TABLE IF EXISTS `cbse_exam_grades`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cbse_exam_grades` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `description` mediumtext NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `cbse_exam_grades_range`
--

DROP TABLE IF EXISTS `cbse_exam_grades_range`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cbse_exam_grades_range` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `cbse_exam_grade_id` int(11) DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `minimum_percentage` float NOT NULL,
  `maximum_percentage` float NOT NULL,
  `description` mediumtext NOT NULL,
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `cbse_exam_grade_id` (`cbse_exam_grade_id`),
  KEY `idx_name` (`name`),
  CONSTRAINT `cbse_exam_grades_range_ibfk_1` FOREIGN KEY (`cbse_exam_grade_id`) REFERENCES `cbse_exam_grades` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `cbse_exam_observations`
--

DROP TABLE IF EXISTS `cbse_exam_observations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cbse_exam_observations` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(200) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `cbse_exam_student_subject_rank`
--

DROP TABLE IF EXISTS `cbse_exam_student_subject_rank`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cbse_exam_student_subject_rank` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `cbse_template_id` int(11) DEFAULT NULL,
  `student_session_id` int(11) DEFAULT NULL,
  `subject_id` int(11) DEFAULT NULL,
  `rank` int(11) DEFAULT NULL,
  `rank_percentage` float(10,2) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `cbse_template_id` (`cbse_template_id`),
  KEY `student_session_id` (`student_session_id`),
  KEY `subject_id` (`subject_id`),
  KEY `idx_rank` (`rank`),
  CONSTRAINT `cbse_exam_student_subject_rank_ibfk_1` FOREIGN KEY (`cbse_template_id`) REFERENCES `cbse_template` (`id`) ON DELETE CASCADE,
  CONSTRAINT `cbse_exam_student_subject_rank_ibfk_2` FOREIGN KEY (`student_session_id`) REFERENCES `student_session` (`id`) ON DELETE CASCADE,
  CONSTRAINT `cbse_exam_student_subject_rank_ibfk_3` FOREIGN KEY (`subject_id`) REFERENCES `subjects` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `cbse_exam_students`
--

DROP TABLE IF EXISTS `cbse_exam_students`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cbse_exam_students` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `cbse_exam_id` int(11) NOT NULL,
  `student_session_id` int(11) NOT NULL,
  `staff_id` int(11) DEFAULT NULL,
  `roll_no` varchar(20) DEFAULT NULL,
  `remark` text DEFAULT NULL,
  `total_present_days` int(11) DEFAULT NULL,
  `delete_student_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `cbse_exam_id` (`cbse_exam_id`),
  KEY `student_session_id` (`student_session_id`),
  CONSTRAINT `cbse_exam_students_ibfk_1` FOREIGN KEY (`cbse_exam_id`) REFERENCES `cbse_exams` (`id`) ON DELETE CASCADE,
  CONSTRAINT `cbse_exam_students_ibfk_2` FOREIGN KEY (`student_session_id`) REFERENCES `student_session` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `cbse_exam_timetable`
--

DROP TABLE IF EXISTS `cbse_exam_timetable`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cbse_exam_timetable` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `cbse_exam_id` int(11) DEFAULT NULL,
  `subject_id` int(11) DEFAULT NULL,
  `date` date NOT NULL,
  `time_from` time NOT NULL,
  `time_to` time NOT NULL,
  `duration` int(11) NOT NULL,
  `room_no` varchar(255) NOT NULL,
  `is_written` int(11) NOT NULL DEFAULT 1,
  `written_maximum_marks` float NOT NULL,
  `is_practical` int(11) NOT NULL,
  `practical_maximum_mark` float DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `cbse_exam_id` (`cbse_exam_id`),
  KEY `subject_id` (`subject_id`),
  CONSTRAINT `cbse_exam_timetable_ibfk_1` FOREIGN KEY (`cbse_exam_id`) REFERENCES `cbse_exams` (`id`) ON DELETE CASCADE,
  CONSTRAINT `cbse_exam_timetable_ibfk_2` FOREIGN KEY (`subject_id`) REFERENCES `subjects` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `cbse_exam_timetable_assessment_types`
--

DROP TABLE IF EXISTS `cbse_exam_timetable_assessment_types`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cbse_exam_timetable_assessment_types` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `cbse_exam_timetable_id` int(11) DEFAULT NULL,
  `cbse_exam_assessment_type_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `cbse_exam_timetable_id` (`cbse_exam_timetable_id`),
  KEY `cbse_exam_assessment_type_id` (`cbse_exam_assessment_type_id`),
  CONSTRAINT `cbse_exam_timetable_assessment_types_ibfk_1` FOREIGN KEY (`cbse_exam_timetable_id`) REFERENCES `cbse_exam_timetable` (`id`) ON DELETE CASCADE,
  CONSTRAINT `cbse_exam_timetable_assessment_types_ibfk_2` FOREIGN KEY (`cbse_exam_assessment_type_id`) REFERENCES `cbse_exam_assessment_types` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `cbse_exams`
--

DROP TABLE IF EXISTS `cbse_exams`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cbse_exams` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `total_working_days` int(11) DEFAULT 0,
  `cbse_term_id` int(11) DEFAULT NULL,
  `cbse_exam_assessment_id` int(11) DEFAULT NULL,
  `cbse_exam_grade_id` int(11) DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `exam_code` varchar(200) DEFAULT NULL,
  `session_id` int(11) NOT NULL,
  `description` mediumtext NOT NULL,
  `is_publish` int(11) NOT NULL,
  `is_active` int(11) NOT NULL,
  `created_by` int(11) DEFAULT NULL,
  `use_exam_roll_no` int(11) NOT NULL DEFAULT 0,
  `cbse_category_id` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `cbse_term_id` (`cbse_term_id`),
  KEY `cbse_exam_grade_id` (`cbse_exam_grade_id`),
  KEY `cbse_exam_assessment_id` (`cbse_exam_assessment_id`),
  KEY `session_id` (`session_id`),
  KEY `idx_name` (`name`),
  KEY `idx_exam_code` (`exam_code`),
  CONSTRAINT `cbse_exams_ibfk_1` FOREIGN KEY (`cbse_term_id`) REFERENCES `cbse_terms` (`id`) ON DELETE CASCADE,
  CONSTRAINT `cbse_exams_ibfk_2` FOREIGN KEY (`cbse_exam_grade_id`) REFERENCES `cbse_exam_grades` (`id`) ON DELETE CASCADE,
  CONSTRAINT `cbse_exams_ibfk_3` FOREIGN KEY (`cbse_exam_assessment_id`) REFERENCES `cbse_exam_assessments` (`id`) ON DELETE CASCADE,
  CONSTRAINT `cbse_exams_ibfk_4` FOREIGN KEY (`session_id`) REFERENCES `sessions` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `cbse_marksheet_type`
--

DROP TABLE IF EXISTS `cbse_marksheet_type`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cbse_marksheet_type` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `short_code` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_name` (`name`),
  KEY `idx_short_code` (`short_code`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `cbse_observation_class_section`
--

DROP TABLE IF EXISTS `cbse_observation_class_section`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cbse_observation_class_section` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `cbse_observation_parameter_id` int(11) NOT NULL,
  `class_section_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `cbse_observation_parameters`
--

DROP TABLE IF EXISTS `cbse_observation_parameters`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cbse_observation_parameters` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `description` mediumtext NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `cbse_observation_subparameter`
--

DROP TABLE IF EXISTS `cbse_observation_subparameter`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cbse_observation_subparameter` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `cbse_exam_observation_id` int(11) NOT NULL,
  `cbse_observation_parameter_id` int(11) NOT NULL,
  `maximum_marks` float NOT NULL,
  `description` mediumtext DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `cbse_observation_parameter_id_ibfk_1` (`cbse_observation_parameter_id`),
  KEY `cbse_exam_observation_id_ibfk_1` (`cbse_exam_observation_id`),
  CONSTRAINT `cbse_exam_observation_id_ibfk_1` FOREIGN KEY (`cbse_exam_observation_id`) REFERENCES `cbse_exam_observations` (`id`) ON DELETE CASCADE,
  CONSTRAINT `cbse_observation_parameter_id_ibfk_1` FOREIGN KEY (`cbse_observation_parameter_id`) REFERENCES `cbse_observation_parameters` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `cbse_observation_term_student_subparameter`
--

DROP TABLE IF EXISTS `cbse_observation_term_student_subparameter`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cbse_observation_term_student_subparameter` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `cbse_ovservation_term_id` int(11) DEFAULT NULL,
  `cbse_observation_subparameter_id` int(11) DEFAULT NULL,
  `student_session_id` int(11) DEFAULT NULL,
  `obtain_marks` float(10,2) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `cbse_observation_term_student_subparameter_ibfk_1` (`cbse_ovservation_term_id`),
  KEY `cbse_observation_subparameter_id` (`cbse_observation_subparameter_id`),
  KEY `student_session_id` (`student_session_id`),
  CONSTRAINT `cbse_observation_term_student_subparameter_ibfk_1` FOREIGN KEY (`cbse_ovservation_term_id`) REFERENCES `cbse_observation_terms` (`id`) ON DELETE CASCADE,
  CONSTRAINT `cbse_observation_term_student_subparameter_ibfk_2` FOREIGN KEY (`cbse_observation_subparameter_id`) REFERENCES `cbse_observation_subparameter` (`id`) ON DELETE CASCADE,
  CONSTRAINT `cbse_observation_term_student_subparameter_ibfk_3` FOREIGN KEY (`student_session_id`) REFERENCES `student_session` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `cbse_observation_terms`
--

DROP TABLE IF EXISTS `cbse_observation_terms`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cbse_observation_terms` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `cbse_exam_observation_id` int(11) NOT NULL,
  `cbse_term_id` int(11) NOT NULL,
  `session_id` int(11) NOT NULL,
  `description` text DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `cbse_term_id` (`cbse_term_id`),
  KEY `cbse_ovservation_terms_ibfk_3` (`session_id`),
  KEY `cbse_exam_observations_ibfk_1` (`cbse_exam_observation_id`),
  CONSTRAINT `cbse_exam_observations_ibfk_1` FOREIGN KEY (`cbse_exam_observation_id`) REFERENCES `cbse_exam_observations` (`id`) ON DELETE CASCADE,
  CONSTRAINT `cbse_observation_terms_ibfk_2` FOREIGN KEY (`cbse_term_id`) REFERENCES `cbse_terms` (`id`) ON DELETE CASCADE,
  CONSTRAINT `cbse_observation_terms_ibfk_3` FOREIGN KEY (`session_id`) REFERENCES `sessions` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `cbse_student_exam_ranks`
--

DROP TABLE IF EXISTS `cbse_student_exam_ranks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cbse_student_exam_ranks` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `cbse_exam_id` int(11) NOT NULL,
  `student_session_id` int(11) DEFAULT NULL,
  `rank` int(11) DEFAULT NULL,
  `rank_percentage` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `cbse_exam_id` (`cbse_exam_id`),
  KEY `student_session_id` (`student_session_id`),
  KEY `idx_rank` (`rank`),
  KEY `idx_rank_percentage` (`rank_percentage`),
  CONSTRAINT `cbse_student_exam_ranks_ibfk_1` FOREIGN KEY (`cbse_exam_id`) REFERENCES `cbse_exams` (`id`) ON DELETE CASCADE,
  CONSTRAINT `cbse_student_exam_ranks_ibfk_2` FOREIGN KEY (`student_session_id`) REFERENCES `student_session` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `cbse_student_subject_marks`
--

DROP TABLE IF EXISTS `cbse_student_subject_marks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cbse_student_subject_marks` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `cbse_exam_timetable_assessment_type_id` int(11) NOT NULL,
  `cbse_exam_timetable_id` int(11) DEFAULT NULL,
  `cbse_exam_student_id` int(11) DEFAULT NULL,
  `cbse_exam_assessment_type_id` int(11) DEFAULT NULL,
  `is_absent` int(11) NOT NULL DEFAULT 0,
  `marks` float(10,2) DEFAULT 0.00,
  `note` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `cbse_exam_timetable_id` (`cbse_exam_timetable_id`),
  KEY `cbse_exam_student_id` (`cbse_exam_student_id`),
  KEY `cbse_exam_assessment_type_id` (`cbse_exam_assessment_type_id`),
  KEY `cbse_exam_timetable_assessment_type_ibfk_4` (`cbse_exam_timetable_assessment_type_id`),
  CONSTRAINT `cbse_exam_timetable_assessment_type_ibfk_4` FOREIGN KEY (`cbse_exam_timetable_assessment_type_id`) REFERENCES `cbse_exam_timetable_assessment_types` (`id`) ON DELETE CASCADE,
  CONSTRAINT `cbse_student_subject_marks_ibfk_1` FOREIGN KEY (`cbse_exam_timetable_id`) REFERENCES `cbse_exam_timetable` (`id`) ON DELETE CASCADE,
  CONSTRAINT `cbse_student_subject_marks_ibfk_2` FOREIGN KEY (`cbse_exam_student_id`) REFERENCES `cbse_exam_students` (`id`) ON DELETE CASCADE,
  CONSTRAINT `cbse_student_subject_marks_ibfk_3` FOREIGN KEY (`cbse_exam_assessment_type_id`) REFERENCES `cbse_exam_assessment_types` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `cbse_student_subject_result`
--

DROP TABLE IF EXISTS `cbse_student_subject_result`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cbse_student_subject_result` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `cbse_exam_timetable_id` int(11) DEFAULT NULL,
  `cbse_exam_student_id` int(11) DEFAULT NULL,
  `note` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `cbse_student_template_rank`
--

DROP TABLE IF EXISTS `cbse_student_template_rank`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cbse_student_template_rank` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `cbse_template_id` int(11) DEFAULT NULL,
  `student_session_id` int(11) DEFAULT NULL,
  `rank` int(11) DEFAULT NULL,
  `rank_percentage` float(10,2) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `student_session_id` (`student_session_id`),
  KEY `cbse_template_id` (`cbse_template_id`),
  KEY `idx_rank` (`rank`),
  KEY `idx_rank_percentage` (`rank_percentage`),
  CONSTRAINT `cbse_student_template_rank_ibfk_1` FOREIGN KEY (`student_session_id`) REFERENCES `student_session` (`id`) ON DELETE CASCADE,
  CONSTRAINT `cbse_student_template_rank_ibfk_2` FOREIGN KEY (`cbse_template_id`) REFERENCES `cbse_template` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `cbse_template`
--

DROP TABLE IF EXISTS `cbse_template`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cbse_template` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(200) DEFAULT NULL,
  `orientation` varchar(1) NOT NULL DEFAULT 'P',
  `description` varchar(255) NOT NULL,
  `gradeexam_id` int(11) DEFAULT NULL,
  `remarkexam_id` int(11) DEFAULT NULL,
  `subjectnoteexam_id` int(11) NOT NULL,
  `is_weightage` varchar(10) NOT NULL,
  `marksheet_type` varchar(50) NOT NULL,
  `created_by` int(11) NOT NULL,
  `header_image` varbinary(500) DEFAULT NULL,
  `title` text DEFAULT NULL,
  `left_logo` varchar(200) DEFAULT NULL,
  `right_logo` varchar(200) DEFAULT NULL,
  `exam_name` varchar(200) DEFAULT NULL,
  `school_name` varchar(200) DEFAULT NULL,
  `exam_center` varchar(200) DEFAULT NULL,
  `session_id` int(11) NOT NULL,
  `left_sign` varchar(200) DEFAULT NULL,
  `middle_sign` varchar(200) DEFAULT NULL,
  `right_sign` varchar(200) DEFAULT NULL,
  `background_img` varchar(200) DEFAULT NULL,
  `content` text DEFAULT NULL,
  `content_footer` text DEFAULT NULL,
  `date` date DEFAULT NULL,
  `is_name` int(11) DEFAULT 1,
  `is_father_name` int(11) DEFAULT 1,
  `is_mother_name` int(11) DEFAULT 1,
  `exam_session` int(11) DEFAULT 1,
  `is_admission_no` int(11) DEFAULT 1,
  `is_division` int(11) NOT NULL DEFAULT 1,
  `is_roll_no` int(11) DEFAULT 1,
  `is_photo` int(11) DEFAULT 1,
  `is_class` int(11) NOT NULL DEFAULT 0,
  `is_section` int(11) NOT NULL DEFAULT 0,
  `is_dob` int(11) DEFAULT 1,
  `is_remark` int(11) NOT NULL DEFAULT 1,
  `is_subject_note` int(11) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`),
  KEY `cbse_template_ibfk_3` (`session_id`),
  KEY `cbse_template_ibfk_1` (`gradeexam_id`),
  KEY `cbse_template_ibfk_2` (`remarkexam_id`),
  KEY `idx_name` (`name`),
  KEY `idx_marksheet_type` (`marksheet_type`),
  KEY `idx_exam_name` (`exam_name`),
  KEY `idx_school_name` (`school_name`),
  CONSTRAINT `cbse_template_ibfk_1` FOREIGN KEY (`gradeexam_id`) REFERENCES `cbse_exams` (`id`) ON DELETE SET NULL,
  CONSTRAINT `cbse_template_ibfk_2` FOREIGN KEY (`remarkexam_id`) REFERENCES `cbse_exams` (`id`) ON DELETE SET NULL,
  CONSTRAINT `cbse_template_ibfk_3` FOREIGN KEY (`session_id`) REFERENCES `sessions` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `cbse_template_admitcards`
--

DROP TABLE IF EXISTS `cbse_template_admitcards`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cbse_template_admitcards` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `template` varchar(250) DEFAULT NULL,
  `heading` text DEFAULT NULL,
  `title` text DEFAULT NULL,
  `left_logo` varchar(200) DEFAULT NULL,
  `right_logo` varchar(200) DEFAULT NULL,
  `exam_name` varchar(200) DEFAULT NULL,
  `school_name` varchar(200) DEFAULT NULL,
  `exam_center` varchar(200) DEFAULT NULL,
  `sign` varchar(200) DEFAULT NULL,
  `background_img` varchar(200) DEFAULT NULL,
  `is_name` int(11) NOT NULL DEFAULT 1,
  `is_father_name` int(11) NOT NULL DEFAULT 1,
  `is_mother_name` int(11) NOT NULL DEFAULT 1,
  `is_dob` int(11) NOT NULL DEFAULT 1,
  `is_admission_no` int(11) NOT NULL DEFAULT 1,
  `is_roll_no` int(11) NOT NULL DEFAULT 1,
  `is_address` int(11) NOT NULL DEFAULT 1,
  `is_gender` int(11) NOT NULL DEFAULT 1,
  `is_photo` int(11) NOT NULL,
  `is_class` int(11) NOT NULL DEFAULT 0,
  `is_section` int(11) NOT NULL DEFAULT 0,
  `is_active` int(11) DEFAULT 0,
  `content_footer` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `cbse_template_class_sections`
--

DROP TABLE IF EXISTS `cbse_template_class_sections`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cbse_template_class_sections` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `cbse_template_id` int(11) NOT NULL,
  `class_section_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `cbse_template_id` (`cbse_template_id`),
  KEY `class_section_id` (`class_section_id`),
  CONSTRAINT `cbse_template_class_sections_ibfk_1` FOREIGN KEY (`cbse_template_id`) REFERENCES `cbse_template` (`id`) ON DELETE CASCADE,
  CONSTRAINT `cbse_template_class_sections_ibfk_2` FOREIGN KEY (`class_section_id`) REFERENCES `class_sections` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `cbse_template_term_exams`
--

DROP TABLE IF EXISTS `cbse_template_term_exams`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cbse_template_term_exams` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `cbse_template_term_id` int(11) DEFAULT NULL,
  `cbse_exam_id` int(11) NOT NULL,
  `cbse_template_id` int(11) NOT NULL,
  `weightage` float NOT NULL DEFAULT 100,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `cbse_template_term_id` (`cbse_template_term_id`),
  KEY `cbse_template_term_exams_ibfk_3` (`cbse_exam_id`),
  KEY `cbse_template_term_exams_ibfk_4` (`cbse_template_id`),
  KEY `idx_weightage` (`weightage`),
  CONSTRAINT `cbse_template_term_exams_ibfk_1` FOREIGN KEY (`cbse_exam_id`) REFERENCES `cbse_exams` (`id`) ON DELETE CASCADE,
  CONSTRAINT `cbse_template_term_exams_ibfk_2` FOREIGN KEY (`cbse_template_term_id`) REFERENCES `cbse_template_terms` (`id`) ON DELETE CASCADE,
  CONSTRAINT `cbse_template_term_exams_ibfk_4` FOREIGN KEY (`cbse_template_id`) REFERENCES `cbse_template` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `cbse_template_terms`
--

DROP TABLE IF EXISTS `cbse_template_terms`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cbse_template_terms` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `cbse_template_id` int(11) NOT NULL,
  `cbse_term_id` int(11) NOT NULL,
  `weightage` float NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `cbse_template_id` (`cbse_template_id`),
  KEY `cbse_term_id` (`cbse_term_id`),
  KEY `idx_weightage` (`weightage`),
  CONSTRAINT `cbse_template_terms_ibfk_1` FOREIGN KEY (`cbse_template_id`) REFERENCES `cbse_template` (`id`) ON DELETE CASCADE,
  CONSTRAINT `cbse_template_terms_ibfk_2` FOREIGN KEY (`cbse_term_id`) REFERENCES `cbse_terms` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `cbse_terms`
--

DROP TABLE IF EXISTS `cbse_terms`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cbse_terms` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `term_code` varchar(100) NOT NULL,
  `description` mediumtext NOT NULL,
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_name` (`name`),
  KEY `idx_term_code` (`term_code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `certificates`
--

DROP TABLE IF EXISTS `certificates`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `certificates` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `certificate_name` varchar(100) NOT NULL,
  `certificate_text` text NOT NULL,
  `left_header` varchar(100) NOT NULL,
  `center_header` varchar(100) NOT NULL,
  `right_header` varchar(100) NOT NULL,
  `left_footer` varchar(100) NOT NULL,
  `right_footer` varchar(100) NOT NULL,
  `center_footer` varchar(100) NOT NULL,
  `background_image` varchar(100) DEFAULT NULL,
  `created_for` tinyint(1) NOT NULL COMMENT '1 = staff, 2 = students',
  `status` tinyint(1) NOT NULL,
  `header_height` int(11) NOT NULL,
  `content_height` int(11) NOT NULL,
  `footer_height` int(11) NOT NULL,
  `content_width` int(11) NOT NULL,
  `enable_student_image` tinyint(1) NOT NULL COMMENT '0=no,1=yes',
  `enable_image_height` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `chat_connections`
--

DROP TABLE IF EXISTS `chat_connections`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `chat_connections` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `chat_user_one` int(11) NOT NULL,
  `chat_user_two` int(11) NOT NULL,
  `ip` varchar(30) DEFAULT NULL,
  `time` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `chat_user_one` (`chat_user_one`),
  KEY `chat_user_two` (`chat_user_two`),
  CONSTRAINT `chat_connections_ibfk_1` FOREIGN KEY (`chat_user_one`) REFERENCES `chat_users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `chat_connections_ibfk_2` FOREIGN KEY (`chat_user_two`) REFERENCES `chat_users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `chat_messages`
--

DROP TABLE IF EXISTS `chat_messages`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `chat_messages` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `message` text DEFAULT NULL,
  `chat_user_id` int(11) NOT NULL,
  `ip` varchar(30) NOT NULL,
  `time` int(11) NOT NULL,
  `is_first` int(11) DEFAULT 0,
  `is_read` int(11) NOT NULL DEFAULT 0,
  `chat_connection_id` int(11) NOT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `chat_user_id` (`chat_user_id`),
  KEY `chat_connection_id` (`chat_connection_id`),
  CONSTRAINT `chat_messages_ibfk_1` FOREIGN KEY (`chat_user_id`) REFERENCES `chat_users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `chat_messages_ibfk_2` FOREIGN KEY (`chat_connection_id`) REFERENCES `chat_connections` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=39 DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `chat_users`
--

DROP TABLE IF EXISTS `chat_users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `chat_users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_type` varchar(20) DEFAULT NULL,
  `staff_id` int(11) DEFAULT NULL,
  `student_id` int(11) DEFAULT NULL,
  `create_staff_id` int(11) DEFAULT NULL,
  `create_student_id` int(11) DEFAULT NULL,
  `is_active` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `staff_id` (`staff_id`),
  KEY `student_id` (`student_id`),
  KEY `create_staff_id` (`create_staff_id`),
  KEY `create_student_id` (`create_student_id`),
  CONSTRAINT `chat_users_ibfk_1` FOREIGN KEY (`staff_id`) REFERENCES `staff` (`id`) ON DELETE CASCADE,
  CONSTRAINT `chat_users_ibfk_2` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE,
  CONSTRAINT `chat_users_ibfk_3` FOREIGN KEY (`create_staff_id`) REFERENCES `staff` (`id`) ON DELETE CASCADE,
  CONSTRAINT `chat_users_ibfk_4` FOREIGN KEY (`create_student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `class_section_times`
--

DROP TABLE IF EXISTS `class_section_times`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `class_section_times` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `class_section_id` int(11) DEFAULT NULL,
  `time` time DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `class_section_id` (`class_section_id`),
  CONSTRAINT `class_section_times_ibfk_1` FOREIGN KEY (`class_section_id`) REFERENCES `class_sections` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `class_sections`
--

DROP TABLE IF EXISTS `class_sections`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `class_sections` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `class_id` int(11) DEFAULT NULL,
  `section_id` int(11) DEFAULT NULL,
  `is_active` varchar(255) DEFAULT 'no',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `class_id` (`class_id`),
  KEY `section_id` (`section_id`),
  CONSTRAINT `class_sections_ibfk_1` FOREIGN KEY (`class_id`) REFERENCES `classes` (`id`) ON DELETE CASCADE,
  CONSTRAINT `class_sections_ibfk_2` FOREIGN KEY (`section_id`) REFERENCES `sections` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=218 DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `class_teacher`
--

DROP TABLE IF EXISTS `class_teacher`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `class_teacher` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `session_id` int(11) NOT NULL,
  `class_id` int(11) NOT NULL,
  `section_id` int(11) NOT NULL,
  `staff_id` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `class_id` (`class_id`),
  KEY `section_id` (`section_id`),
  KEY `session_id` (`session_id`),
  KEY `staff_id` (`staff_id`),
  CONSTRAINT `class_teacher_ibfk_1` FOREIGN KEY (`class_id`) REFERENCES `classes` (`id`) ON DELETE CASCADE,
  CONSTRAINT `class_teacher_ibfk_2` FOREIGN KEY (`section_id`) REFERENCES `sections` (`id`) ON DELETE CASCADE,
  CONSTRAINT `class_teacher_ibfk_3` FOREIGN KEY (`session_id`) REFERENCES `sessions` (`id`) ON DELETE CASCADE,
  CONSTRAINT `class_teacher_ibfk_4` FOREIGN KEY (`staff_id`) REFERENCES `staff` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `classes`
--

DROP TABLE IF EXISTS `classes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `classes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `department_id` int(11) DEFAULT NULL,
  `class` varchar(60) DEFAULT NULL,
  `is_active` varchar(255) DEFAULT 'no',
  `class_type` enum('academic','applicant') NOT NULL DEFAULT 'academic',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=116 DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `complaint`
--

DROP TABLE IF EXISTS `complaint`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `complaint` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `complaint_type` varchar(255) NOT NULL,
  `source` varchar(255) NOT NULL,
  `name` varchar(100) NOT NULL,
  `contact` varchar(15) NOT NULL,
  `email` varchar(200) NOT NULL,
  `date` date NOT NULL,
  `description` text NOT NULL,
  `action_taken` varchar(200) NOT NULL,
  `assigned` varchar(50) NOT NULL,
  `note` text NOT NULL,
  `image` varchar(100) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `complaint_type`
--

DROP TABLE IF EXISTS `complaint_type`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `complaint_type` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `complaint_type` varchar(100) NOT NULL,
  `description` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `conference_sections`
--

DROP TABLE IF EXISTS `conference_sections`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `conference_sections` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `conference_id` int(11) DEFAULT NULL,
  `cls_section_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `conference_sections_ibfk_1` (`conference_id`),
  KEY `conference_sections_ibfk_2` (`cls_section_id`),
  CONSTRAINT `conference_sections_ibfk_1` FOREIGN KEY (`conference_id`) REFERENCES `conferences` (`id`) ON DELETE CASCADE,
  CONSTRAINT `conference_sections_ibfk_2` FOREIGN KEY (`cls_section_id`) REFERENCES `class_sections` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `conference_staff`
--

DROP TABLE IF EXISTS `conference_staff`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `conference_staff` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `conference_id` int(11) NOT NULL,
  `staff_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `conference_staff_ibfk_1` (`conference_id`),
  KEY `conference_staff_ibfk_2` (`staff_id`),
  CONSTRAINT `conference_staff_ibfk_1` FOREIGN KEY (`conference_id`) REFERENCES `conferences` (`id`) ON DELETE CASCADE,
  CONSTRAINT `conference_staff_ibfk_2` FOREIGN KEY (`staff_id`) REFERENCES `staff` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `conferences`
--

DROP TABLE IF EXISTS `conferences`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `conferences` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `purpose` varchar(20) NOT NULL DEFAULT 'class',
  `staff_id` int(11) DEFAULT NULL,
  `created_id` int(11) NOT NULL,
  `title` text DEFAULT NULL,
  `date` datetime DEFAULT NULL,
  `duration` int(11) DEFAULT NULL,
  `password` varchar(50) DEFAULT NULL,
  `subject` varchar(50) DEFAULT NULL,
  `class_id` int(11) DEFAULT NULL,
  `section_id` int(11) DEFAULT NULL,
  `session_id` int(11) NOT NULL,
  `host_video` int(11) NOT NULL DEFAULT 1,
  `client_video` int(11) NOT NULL DEFAULT 1,
  `description` text DEFAULT NULL,
  `timezone` varchar(100) DEFAULT NULL,
  `return_response` text DEFAULT NULL,
  `api_type` varchar(30) NOT NULL,
  `status` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `conferences_ibfk_1` (`staff_id`),
  KEY `conferences_ibfk_2` (`created_id`),
  KEY `idx_class_id` (`class_id`),
  CONSTRAINT `conferences_ibfk_1` FOREIGN KEY (`staff_id`) REFERENCES `staff` (`id`) ON DELETE CASCADE,
  CONSTRAINT `conferences_ibfk_2` FOREIGN KEY (`created_id`) REFERENCES `staff` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `conferences_history`
--

DROP TABLE IF EXISTS `conferences_history`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `conferences_history` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `conference_id` int(11) NOT NULL,
  `staff_id` int(11) DEFAULT NULL,
  `student_id` int(11) DEFAULT NULL,
  `total_hit` int(11) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `conferences_history_ibfk_1` (`conference_id`),
  KEY `conferences_history_ibfk_2` (`staff_id`),
  KEY `conferences_history_ibfk_3` (`student_id`),
  CONSTRAINT `conferences_history_ibfk_1` FOREIGN KEY (`conference_id`) REFERENCES `conferences` (`id`) ON DELETE CASCADE,
  CONSTRAINT `conferences_history_ibfk_2` FOREIGN KEY (`staff_id`) REFERENCES `staff` (`id`) ON DELETE CASCADE,
  CONSTRAINT `conferences_history_ibfk_3` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `content_for`
--

DROP TABLE IF EXISTS `content_for`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `content_for` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `role` varchar(50) DEFAULT NULL,
  `content_id` int(11) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `content_id` (`content_id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `content_for_ibfk_1` FOREIGN KEY (`content_id`) REFERENCES `contents` (`id`) ON DELETE CASCADE,
  CONSTRAINT `content_for_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `content_types`
--

DROP TABLE IF EXISTS `content_types`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `content_types` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(200) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `is_active` int(11) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `content_types`
--

INSERT INTO `content_types` (`id`, `name`, `description`, `is_active`) VALUES
(1,'Lecture Notes','Class lecture notes and study material',1),
(2,'Study Material','Reference books, guides and supplementary material',1),
(3,'Assignment','Assignment sheets and worksheets',1),
(4,'Question Paper','Previous year and sample question papers',1),
(5,'Lab Manual','Laboratory manuals and practical guides',1),
(6,'Video Lecture','Recorded video lectures',1),
(7,'Project Report','Project reports and case studies',1),
(8,'Others','Miscellaneous content',1);

--
-- Table structure for table `contents`
--

DROP TABLE IF EXISTS `contents`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `contents` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(100) DEFAULT NULL,
  `type` varchar(50) DEFAULT NULL,
  `is_public` varchar(10) DEFAULT 'No',
  `class_id` int(11) DEFAULT NULL,
  `cls_sec_id` int(11) DEFAULT NULL,
  `file` varchar(250) DEFAULT NULL,
  `date` date NOT NULL,
  `note` text DEFAULT NULL,
  `is_active` varchar(255) DEFAULT 'no',
  `created_by` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `created_by` (`created_by`),
  KEY `class_id` (`class_id`),
  KEY `cls_sec_id` (`cls_sec_id`),
  CONSTRAINT `contents_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `staff` (`id`) ON DELETE CASCADE,
  CONSTRAINT `contents_ibfk_2` FOREIGN KEY (`class_id`) REFERENCES `classes` (`id`) ON DELETE CASCADE,
  CONSTRAINT `contents_ibfk_3` FOREIGN KEY (`cls_sec_id`) REFERENCES `class_sections` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `cumulative_fine`
--

DROP TABLE IF EXISTS `cumulative_fine`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cumulative_fine` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `overdue_day` int(11) NOT NULL,
  `fine_amount` float(10,2) NOT NULL,
  `fee_groups_feetype_id` int(11) NOT NULL,
  `fee_session_group_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `currencies`
--

DROP TABLE IF EXISTS `currencies`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `currencies` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) DEFAULT NULL,
  `short_name` varchar(100) DEFAULT NULL,
  `symbol` varchar(10) DEFAULT NULL,
  `base_price` varchar(10) NOT NULL DEFAULT '1',
  `is_active` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=180 DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `custom_field_values`
--

DROP TABLE IF EXISTS `custom_field_values`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `custom_field_values` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `belong_table_id` int(11) DEFAULT NULL,
  `custom_field_id` int(11) DEFAULT NULL,
  `field_value` varchar(500) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `custom_field_id` (`custom_field_id`),
  KEY `idx_belong_table_id` (`belong_table_id`),
  KEY `idx_field_value` (`field_value`),
  CONSTRAINT `custom_field_values_ibfk_1` FOREIGN KEY (`custom_field_id`) REFERENCES `custom_fields` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `custom_fields`
--

DROP TABLE IF EXISTS `custom_fields`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `custom_fields` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) DEFAULT NULL,
  `belong_to` varchar(100) DEFAULT NULL,
  `type` varchar(100) DEFAULT NULL,
  `bs_column` int(11) DEFAULT NULL,
  `validation` int(11) DEFAULT 0,
  `field_values` text DEFAULT NULL,
  `show_table` varchar(100) DEFAULT NULL,
  `visible_on_table` int(11) NOT NULL,
  `weight` int(11) DEFAULT NULL,
  `is_active` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_name` (`name`),
  KEY `idx_belong_to` (`belong_to`),
  KEY `idx_type` (`type`),
  KEY `idx_visible_on_table` (`visible_on_table`),
  KEY `idx_weight` (`weight`),
  FULLTEXT KEY `idx_field_values` (`field_values`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `daily_assignment`
--

DROP TABLE IF EXISTS `daily_assignment`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `daily_assignment` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `student_session_id` int(11) NOT NULL,
  `subject_group_subject_id` int(11) NOT NULL,
  `title` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `attachment` varchar(255) DEFAULT NULL,
  `evaluated_by` int(11) DEFAULT NULL,
  `date` date DEFAULT NULL,
  `evaluation_date` date DEFAULT NULL,
  `remark` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `student_session_id` (`student_session_id`),
  KEY `evaluated_by` (`evaluated_by`),
  KEY `subject_group_subject_id` (`subject_group_subject_id`),
  CONSTRAINT `daily_assignment_ibfk_1` FOREIGN KEY (`student_session_id`) REFERENCES `student_session` (`id`) ON DELETE CASCADE,
  CONSTRAINT `daily_assignment_ibfk_2` FOREIGN KEY (`evaluated_by`) REFERENCES `staff` (`id`) ON DELETE CASCADE,
  CONSTRAINT `daily_assignment_ibfk_3` FOREIGN KEY (`subject_group_subject_id`) REFERENCES `subject_group_subjects` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `department`
--

DROP TABLE IF EXISTS `department`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `department` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `department_name` varchar(200) NOT NULL,
  `is_active` varchar(100) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `dept_head_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=22 DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `disable_reason`
--

DROP TABLE IF EXISTS `disable_reason`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `disable_reason` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `reason` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `dispatch_receive`
--

DROP TABLE IF EXISTS `dispatch_receive`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `dispatch_receive` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `reference_no` varchar(50) NOT NULL,
  `to_title` varchar(100) NOT NULL,
  `type` varchar(10) NOT NULL,
  `address` varchar(500) NOT NULL,
  `note` varchar(500) NOT NULL,
  `from_title` varchar(200) NOT NULL,
  `date` date DEFAULT NULL,
  `image` varchar(100) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `email_attachments`
--

DROP TABLE IF EXISTS `email_attachments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `email_attachments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `message_id` int(11) NOT NULL,
  `directory` varchar(255) NOT NULL,
  `attachment` varchar(255) NOT NULL,
  `attachment_name` varchar(200) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `message_id` (`message_id`),
  CONSTRAINT `email_attachments_ibfk_1` FOREIGN KEY (`message_id`) REFERENCES `messages` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `email_config`
--

DROP TABLE IF EXISTS `email_config`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `email_config` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `email_type` varchar(100) DEFAULT NULL,
  `smtp_server` varchar(100) DEFAULT NULL,
  `smtp_port` varchar(100) DEFAULT NULL,
  `smtp_email` varchar(255) DEFAULT NULL,
  `smtp_username` varchar(100) DEFAULT NULL,
  `smtp_password` varchar(100) DEFAULT NULL,
  `ssl_tls` varchar(100) DEFAULT NULL,
  `smtp_auth` varchar(10) NOT NULL,
  `api_key` varchar(255) DEFAULT NULL,
  `api_secret` varchar(255) DEFAULT NULL,
  `region` varchar(255) DEFAULT NULL,
  `is_active` varchar(10) NOT NULL DEFAULT 'no',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `email_template`
--

DROP TABLE IF EXISTS `email_template`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `email_template` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(100) NOT NULL,
  `message` text NOT NULL,
  `created_at` date NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `email_template_attachment`
--

DROP TABLE IF EXISTS `email_template_attachment`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `email_template_attachment` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `email_template_id` int(11) NOT NULL,
  `attachment` varchar(100) NOT NULL,
  `attachment_name` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `enquiry`
--

DROP TABLE IF EXISTS `enquiry`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `enquiry` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `contact` varchar(20) NOT NULL,
  `address` text NOT NULL,
  `state` varchar(100) DEFAULT NULL,
  `city` varchar(100) DEFAULT NULL,
  `reference` varchar(20) NOT NULL,
  `date` date NOT NULL,
  `description` varchar(500) NOT NULL,
  `follow_up_date` date NOT NULL,
  `note` text NOT NULL,
  `source` varchar(50) NOT NULL,
  `lead_vendor_id` int(11) DEFAULT NULL,
  `duplicate_source_vendor_id` int(11) DEFAULT NULL,
  `email` varchar(50) DEFAULT NULL,
  `assigned` int(11) DEFAULT NULL,
  `class_id` int(11) DEFAULT NULL,
  `admission_course_id` int(11) DEFAULT NULL,
  `course_level` enum('ug','pg') DEFAULT NULL,
  `admission_type` enum('first_year','lateral') DEFAULT NULL,
  `no_of_child` varchar(11) DEFAULT NULL,
  `status` varchar(100) NOT NULL,
  `ref_no` varchar(32) NOT NULL,
  `created_by` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `reference_name` varchar(255) DEFAULT NULL,
  `reference_contact` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `created_by` (`created_by`),
  KEY `assigned` (`assigned`),
  KEY `enquiry_ibfk_4` (`class_id`),
  KEY `idx_enquiry_admission_course` (`admission_course_id`),
  KEY `idx_enquiry_lead_vendor_id` (`lead_vendor_id`),
  KEY `idx_enquiry_duplicate_source_vendor_id` (`duplicate_source_vendor_id`),
  CONSTRAINT `enquiry_ibfk_2` FOREIGN KEY (`created_by`) REFERENCES `staff` (`id`) ON DELETE CASCADE,
  CONSTRAINT `enquiry_ibfk_3` FOREIGN KEY (`assigned`) REFERENCES `staff` (`id`) ON DELETE CASCADE,
  CONSTRAINT `enquiry_ibfk_4` FOREIGN KEY (`class_id`) REFERENCES `classes` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_enquiry_admission_course` FOREIGN KEY (`admission_course_id`) REFERENCES `online_admission_courses` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=4224 DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `enquiry_type`
--

DROP TABLE IF EXISTS `enquiry_type`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `enquiry_type` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `enquiry_type` varchar(100) NOT NULL,
  `description` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `events`
--

DROP TABLE IF EXISTS `events`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `events` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `event_title` varchar(200) NOT NULL,
  `event_description` text NOT NULL,
  `start_date` datetime NOT NULL,
  `end_date` datetime NOT NULL,
  `event_type` varchar(100) NOT NULL,
  `event_color` varchar(200) NOT NULL,
  `event_for` varchar(100) NOT NULL,
  `role_id` int(11) DEFAULT NULL,
  `is_active` varchar(100) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `role_id` (`role_id`),
  CONSTRAINT `events_ibfk_1` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `exam_group_class_batch_exam_students`
--

DROP TABLE IF EXISTS `exam_group_class_batch_exam_students`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `exam_group_class_batch_exam_students` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `exam_group_class_batch_exam_id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `student_session_id` int(11) NOT NULL,
  `roll_no` int(11) DEFAULT NULL,
  `teacher_remark` text DEFAULT NULL,
  `rank` int(11) NOT NULL DEFAULT 0,
  `is_active` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `exam_group_class_batch_exam_id` (`exam_group_class_batch_exam_id`),
  KEY `student_id` (`student_id`),
  KEY `student_session_id` (`student_session_id`),
  CONSTRAINT `exam_group_class_batch_exam_students_ibfk_1` FOREIGN KEY (`exam_group_class_batch_exam_id`) REFERENCES `exam_group_class_batch_exams` (`id`) ON DELETE CASCADE,
  CONSTRAINT `exam_group_class_batch_exam_students_ibfk_2` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE,
  CONSTRAINT `exam_group_class_batch_exam_students_ibfk_3` FOREIGN KEY (`student_session_id`) REFERENCES `student_session` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `exam_group_class_batch_exam_subjects`
--

DROP TABLE IF EXISTS `exam_group_class_batch_exam_subjects`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `exam_group_class_batch_exam_subjects` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `exam_group_class_batch_exams_id` int(11) DEFAULT NULL,
  `subject_id` int(11) NOT NULL,
  `date_from` date NOT NULL,
  `time_from` time NOT NULL,
  `duration` varchar(50) NOT NULL,
  `room_no` varchar(100) DEFAULT NULL,
  `max_marks` float(10,2) DEFAULT NULL,
  `min_marks` float(10,2) DEFAULT NULL,
  `credit_hours` float(10,2) DEFAULT 0.00,
  `date_to` datetime DEFAULT NULL,
  `is_active` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `exam_group_class_batch_exams_id` (`exam_group_class_batch_exams_id`),
  KEY `subject_id` (`subject_id`),
  CONSTRAINT `exam_group_class_batch_exam_subjects_ibfk_1` FOREIGN KEY (`exam_group_class_batch_exams_id`) REFERENCES `exam_group_class_batch_exams` (`id`) ON DELETE CASCADE,
  CONSTRAINT `exam_group_class_batch_exam_subjects_ibfk_2` FOREIGN KEY (`subject_id`) REFERENCES `subjects` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `exam_group_class_batch_exams`
--

DROP TABLE IF EXISTS `exam_group_class_batch_exams`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `exam_group_class_batch_exams` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `exam` varchar(250) DEFAULT NULL,
  `passing_percentage` float(10,2) DEFAULT NULL,
  `session_id` int(11) NOT NULL,
  `date_from` date DEFAULT NULL,
  `date_to` date DEFAULT NULL,
  `exam_group_id` int(11) DEFAULT NULL,
  `use_exam_roll_no` int(11) NOT NULL DEFAULT 1,
  `is_publish` int(11) DEFAULT 0,
  `is_rank_generated` int(11) NOT NULL DEFAULT 0,
  `description` text DEFAULT NULL,
  `is_active` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `exam_group_id` (`exam_group_id`),
  KEY `exam_group_class_batch_exams_ibfk_2` (`session_id`),
  CONSTRAINT `exam_group_class_batch_exams_ibfk_1` FOREIGN KEY (`exam_group_id`) REFERENCES `exam_groups` (`id`) ON DELETE CASCADE,
  CONSTRAINT `exam_group_class_batch_exams_ibfk_2` FOREIGN KEY (`session_id`) REFERENCES `sessions` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `exam_group_exam_connections`
--

DROP TABLE IF EXISTS `exam_group_exam_connections`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `exam_group_exam_connections` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `exam_group_id` int(11) DEFAULT NULL,
  `exam_group_class_batch_exams_id` int(11) DEFAULT NULL,
  `exam_weightage` float(10,2) DEFAULT 0.00,
  `is_active` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `exam_group_id` (`exam_group_id`),
  KEY `exam_group_class_batch_exams_id` (`exam_group_class_batch_exams_id`),
  CONSTRAINT `exam_group_exam_connections_ibfk_1` FOREIGN KEY (`exam_group_id`) REFERENCES `exam_groups` (`id`) ON DELETE CASCADE,
  CONSTRAINT `exam_group_exam_connections_ibfk_2` FOREIGN KEY (`exam_group_class_batch_exams_id`) REFERENCES `exam_group_class_batch_exams` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `exam_group_exam_results`
--

DROP TABLE IF EXISTS `exam_group_exam_results`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `exam_group_exam_results` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `exam_group_class_batch_exam_student_id` int(11) NOT NULL,
  `exam_group_class_batch_exam_subject_id` int(11) DEFAULT NULL,
  `exam_group_student_id` int(11) DEFAULT NULL,
  `attendence` varchar(10) DEFAULT NULL,
  `get_marks` float(10,2) DEFAULT 0.00,
  `note` text DEFAULT NULL,
  `is_active` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `exam_group_class_batch_exam_subject_id` (`exam_group_class_batch_exam_subject_id`),
  KEY `exam_group_student_id` (`exam_group_student_id`),
  KEY `exam_group_class_batch_exam_student_id` (`exam_group_class_batch_exam_student_id`),
  CONSTRAINT `exam_group_exam_results_ibfk_1` FOREIGN KEY (`exam_group_class_batch_exam_subject_id`) REFERENCES `exam_group_class_batch_exam_subjects` (`id`) ON DELETE CASCADE,
  CONSTRAINT `exam_group_exam_results_ibfk_2` FOREIGN KEY (`exam_group_student_id`) REFERENCES `exam_group_students` (`id`) ON DELETE CASCADE,
  CONSTRAINT `exam_group_exam_results_ibfk_3` FOREIGN KEY (`exam_group_class_batch_exam_student_id`) REFERENCES `exam_group_class_batch_exam_students` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `exam_group_students`
--

DROP TABLE IF EXISTS `exam_group_students`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `exam_group_students` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `exam_group_id` int(11) DEFAULT NULL,
  `student_id` int(11) DEFAULT NULL,
  `student_session_id` int(11) DEFAULT NULL,
  `is_active` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `exam_group_id` (`exam_group_id`),
  KEY `student_id` (`student_id`),
  KEY `student_session_id` (`student_session_id`),
  CONSTRAINT `exam_group_students_ibfk_1` FOREIGN KEY (`exam_group_id`) REFERENCES `exam_groups` (`id`) ON DELETE CASCADE,
  CONSTRAINT `exam_group_students_ibfk_2` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE,
  CONSTRAINT `exam_group_students_ibfk_3` FOREIGN KEY (`student_session_id`) REFERENCES `student_session` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `exam_groups`
--

DROP TABLE IF EXISTS `exam_groups`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `exam_groups` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(250) DEFAULT NULL,
  `exam_type` varchar(250) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `is_active` int(11) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `exam_schedules`
--

DROP TABLE IF EXISTS `exam_schedules`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `exam_schedules` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `session_id` int(11) NOT NULL,
  `exam_id` int(11) DEFAULT NULL,
  `teacher_subject_id` int(11) DEFAULT NULL,
  `date_of_exam` date DEFAULT NULL,
  `start_to` varchar(50) DEFAULT NULL,
  `end_from` varchar(50) DEFAULT NULL,
  `room_no` varchar(50) DEFAULT NULL,
  `full_marks` int(11) DEFAULT NULL,
  `passing_marks` int(11) DEFAULT NULL,
  `note` text DEFAULT NULL,
  `is_active` varchar(255) DEFAULT 'no',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `teacher_subject_id` (`teacher_subject_id`),
  KEY `session_id` (`session_id`),
  KEY `exam_id` (`exam_id`),
  CONSTRAINT `exam_schedules_ibfk_1` FOREIGN KEY (`session_id`) REFERENCES `sessions` (`id`) ON DELETE CASCADE,
  CONSTRAINT `exam_schedules_ibfk_2` FOREIGN KEY (`exam_id`) REFERENCES `exams` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `exams`
--

DROP TABLE IF EXISTS `exams`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `exams` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(200) DEFAULT NULL,
  `sesion_id` int(11) NOT NULL,
  `note` text DEFAULT NULL,
  `is_active` varchar(255) DEFAULT 'no',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `sesion_id` (`sesion_id`),
  CONSTRAINT `exams_ibfk_1` FOREIGN KEY (`sesion_id`) REFERENCES `sessions` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `expense_head`
--

DROP TABLE IF EXISTS `expense_head`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `expense_head` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `exp_category` varchar(50) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `is_active` varchar(255) DEFAULT 'yes',
  `is_deleted` varchar(255) DEFAULT 'no',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `expenses`
--

DROP TABLE IF EXISTS `expenses`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `expenses` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `exp_head_id` int(11) DEFAULT NULL,
  `name` varchar(50) DEFAULT NULL,
  `invoice_no` varchar(200) DEFAULT NULL,
  `date` date DEFAULT NULL,
  `amount` float(10,2) DEFAULT NULL,
  `documents` varchar(255) DEFAULT NULL,
  `note` text DEFAULT NULL,
  `is_active` varchar(255) DEFAULT 'yes',
  `is_deleted` varchar(255) DEFAULT 'no',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `exp_head_id` (`exp_head_id`),
  CONSTRAINT `expenses_ibfk_1` FOREIGN KEY (`exp_head_id`) REFERENCES `expense_head` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `fee_groups`
--

DROP TABLE IF EXISTS `fee_groups`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fee_groups` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(200) DEFAULT NULL,
  `is_system` int(11) NOT NULL DEFAULT 0,
  `description` text DEFAULT NULL,
  `nature` varchar(255) NOT NULL,
  `is_active` varchar(10) NOT NULL DEFAULT 'no',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=82 DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `fee_groups_feetype`
--

DROP TABLE IF EXISTS `fee_groups_feetype`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fee_groups_feetype` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `fee_session_group_id` int(11) DEFAULT NULL,
  `fee_groups_id` int(11) DEFAULT NULL,
  `feetype_id` int(11) DEFAULT NULL,
  `session_id` int(11) DEFAULT NULL,
  `amount` decimal(10,2) DEFAULT NULL,
  `fine_type` varchar(50) NOT NULL DEFAULT 'none',
  `due_date` date DEFAULT NULL,
  `fine_percentage` float(10,2) NOT NULL DEFAULT 0.00,
  `fine_amount` float(10,2) NOT NULL DEFAULT 0.00,
  `fine_per_day` int(11) NOT NULL DEFAULT 0,
  `is_active` varchar(10) NOT NULL DEFAULT 'no',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `fee_session_group_id` (`fee_session_group_id`),
  KEY `fee_groups_id` (`fee_groups_id`),
  KEY `feetype_id` (`feetype_id`),
  KEY `session_id` (`session_id`),
  CONSTRAINT `fee_groups_feetype_ibfk_1` FOREIGN KEY (`fee_session_group_id`) REFERENCES `fee_session_groups` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fee_groups_feetype_ibfk_2` FOREIGN KEY (`fee_groups_id`) REFERENCES `fee_groups` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fee_groups_feetype_ibfk_3` FOREIGN KEY (`feetype_id`) REFERENCES `feetype` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fee_groups_feetype_ibfk_4` FOREIGN KEY (`session_id`) REFERENCES `sessions` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=158 DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `fee_receipt_no`
--

DROP TABLE IF EXISTS `fee_receipt_no`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fee_receipt_no` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `payment` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `fee_session_groups`
--

DROP TABLE IF EXISTS `fee_session_groups`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fee_session_groups` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `fee_groups_id` int(11) DEFAULT NULL,
  `session_id` int(11) DEFAULT NULL,
  `is_active` varchar(10) NOT NULL DEFAULT 'no',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `fee_groups_id` (`fee_groups_id`),
  KEY `session_id` (`session_id`),
  CONSTRAINT `fee_session_groups_ibfk_1` FOREIGN KEY (`fee_groups_id`) REFERENCES `fee_groups` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fee_session_groups_ibfk_2` FOREIGN KEY (`session_id`) REFERENCES `sessions` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=82 DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `feemasters`
--

DROP TABLE IF EXISTS `feemasters`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `feemasters` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `session_id` int(11) DEFAULT NULL,
  `feetype_id` int(11) NOT NULL,
  `class_id` int(11) DEFAULT NULL,
  `amount` float(10,2) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `is_active` varchar(255) DEFAULT 'no',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `session_id` (`session_id`),
  KEY `feetype_id` (`feetype_id`),
  KEY `class_id` (`class_id`),
  CONSTRAINT `feemasters_ibfk_1` FOREIGN KEY (`session_id`) REFERENCES `sessions` (`id`) ON DELETE CASCADE,
  CONSTRAINT `feemasters_ibfk_2` FOREIGN KEY (`feetype_id`) REFERENCES `feetype` (`id`) ON DELETE CASCADE,
  CONSTRAINT `feemasters_ibfk_3` FOREIGN KEY (`class_id`) REFERENCES `classes` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `fees_discounts`
--

DROP TABLE IF EXISTS `fees_discounts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fees_discounts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `session_id` int(11) DEFAULT NULL,
  `name` varchar(100) DEFAULT NULL,
  `code` varchar(100) DEFAULT NULL,
  `type` varchar(20) DEFAULT NULL,
  `percentage` float(10,2) DEFAULT NULL,
  `amount` float(10,2) DEFAULT NULL,
  `discount_limit` int(11) DEFAULT NULL,
  `expire_date` date DEFAULT NULL,
  `description` text DEFAULT NULL,
  `is_active` varchar(10) NOT NULL DEFAULT 'no',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `session_id` (`session_id`),
  CONSTRAINT `fees_discounts_ibfk_1` FOREIGN KEY (`session_id`) REFERENCES `sessions` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=18 DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `fees_reminder`
--

DROP TABLE IF EXISTS `fees_reminder`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fees_reminder` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `reminder_type` varchar(10) DEFAULT NULL,
  `day` int(11) DEFAULT NULL,
  `is_active` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `feetype`
--

DROP TABLE IF EXISTS `feetype`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `feetype` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `is_system` int(11) NOT NULL DEFAULT 0,
  `feecategory_id` int(11) DEFAULT NULL,
  `type` varchar(50) DEFAULT NULL,
  `code` varchar(100) NOT NULL,
  `is_active` varchar(255) DEFAULT 'no',
  `description` text DEFAULT NULL,
  `session_id` int(11) DEFAULT NULL,
  `student_session_id` int(11) DEFAULT NULL,
  `nature` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp(),
  `sub_merchant_id` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `filetypes`
--

DROP TABLE IF EXISTS `filetypes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `filetypes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `file_extension` text DEFAULT NULL,
  `file_mime` text DEFAULT NULL,
  `file_size` int(11) NOT NULL,
  `image_extension` text DEFAULT NULL,
  `image_mime` text DEFAULT NULL,
  `image_size` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `final_year_classes`
--

DROP TABLE IF EXISTS `final_year_classes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `final_year_classes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `class_id` int(11) NOT NULL,
  `created_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_final_year_class` (`class_id`)
) ENGINE=InnoDB AUTO_INCREMENT=18 DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `follow_up`
--

DROP TABLE IF EXISTS `follow_up`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `follow_up` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `enquiry_id` int(11) NOT NULL,
  `date` date NOT NULL,
  `next_date` date NOT NULL,
  `response` text NOT NULL,
  `note` text NOT NULL,
  `followup_by` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `enquiry_id` (`enquiry_id`),
  KEY `followup_by` (`followup_by`),
  CONSTRAINT `follow_up_ibfk_1` FOREIGN KEY (`enquiry_id`) REFERENCES `enquiry` (`id`) ON DELETE CASCADE,
  CONSTRAINT `follow_up_ibfk_2` FOREIGN KEY (`followup_by`) REFERENCES `staff` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=4452 DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `front_cms_media_gallery`
--

DROP TABLE IF EXISTS `front_cms_media_gallery`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `front_cms_media_gallery` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `image` varchar(300) DEFAULT NULL,
  `thumb_path` varchar(300) DEFAULT NULL,
  `dir_path` varchar(300) DEFAULT NULL,
  `img_name` varchar(300) DEFAULT NULL,
  `thumb_name` varchar(300) DEFAULT NULL,
  `file_type` varchar(100) NOT NULL,
  `file_size` varchar(100) NOT NULL,
  `vid_url` text NOT NULL,
  `vid_title` varchar(250) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `front_cms_menu_items`
--

DROP TABLE IF EXISTS `front_cms_menu_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `front_cms_menu_items` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `menu_id` int(11) NOT NULL,
  `menu` varchar(100) DEFAULT NULL,
  `page_id` int(11) NOT NULL,
  `parent_id` int(11) NOT NULL,
  `ext_url` text DEFAULT NULL,
  `open_new_tab` int(11) DEFAULT 0,
  `ext_url_link` text DEFAULT NULL,
  `slug` varchar(200) DEFAULT NULL,
  `weight` int(11) DEFAULT NULL,
  `publish` int(11) NOT NULL DEFAULT 0,
  `description` text DEFAULT NULL,
  `is_active` varchar(10) DEFAULT 'no',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `menu_id` (`menu_id`),
  CONSTRAINT `front_cms_menu_items_ibfk_1` FOREIGN KEY (`menu_id`) REFERENCES `front_cms_menus` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `front_cms_menus`
--

DROP TABLE IF EXISTS `front_cms_menus`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `front_cms_menus` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `menu` varchar(100) DEFAULT NULL,
  `slug` varchar(200) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `open_new_tab` int(11) NOT NULL DEFAULT 0,
  `ext_url` text NOT NULL,
  `ext_url_link` text NOT NULL,
  `publish` int(11) NOT NULL DEFAULT 0,
  `content_type` varchar(10) NOT NULL DEFAULT 'manual',
  `is_active` varchar(10) DEFAULT 'no',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `front_cms_page_contents`
--

DROP TABLE IF EXISTS `front_cms_page_contents`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `front_cms_page_contents` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `page_id` int(11) DEFAULT NULL,
  `content_type` varchar(50) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `page_id` (`page_id`),
  CONSTRAINT `front_cms_page_contents_ibfk_1` FOREIGN KEY (`page_id`) REFERENCES `front_cms_pages` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `front_cms_pages`
--

DROP TABLE IF EXISTS `front_cms_pages`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `front_cms_pages` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `page_type` varchar(10) NOT NULL DEFAULT 'manual',
  `is_homepage` int(11) DEFAULT 0,
  `title` varchar(250) DEFAULT NULL,
  `url` varchar(250) DEFAULT NULL,
  `type` varchar(50) DEFAULT NULL,
  `slug` varchar(200) DEFAULT NULL,
  `meta_title` text DEFAULT NULL,
  `meta_description` text DEFAULT NULL,
  `meta_keyword` text DEFAULT NULL,
  `feature_image` varchar(200) NOT NULL,
  `description` longtext DEFAULT NULL,
  `publish_date` date DEFAULT NULL,
  `publish` int(11) DEFAULT 0,
  `sidebar` int(11) DEFAULT 0,
  `is_active` varchar(10) DEFAULT 'no',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `front_cms_program_photos`
--

DROP TABLE IF EXISTS `front_cms_program_photos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `front_cms_program_photos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `program_id` int(11) DEFAULT NULL,
  `media_gallery_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `program_id` (`program_id`),
  CONSTRAINT `front_cms_program_photos_ibfk_1` FOREIGN KEY (`program_id`) REFERENCES `front_cms_programs` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `front_cms_programs`
--

DROP TABLE IF EXISTS `front_cms_programs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `front_cms_programs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `type` varchar(50) DEFAULT NULL,
  `slug` varchar(255) DEFAULT NULL,
  `url` text DEFAULT NULL,
  `title` varchar(200) DEFAULT NULL,
  `date` date DEFAULT NULL,
  `event_start` date DEFAULT NULL,
  `event_end` date DEFAULT NULL,
  `event_venue` text DEFAULT NULL,
  `description` text DEFAULT NULL,
  `is_active` varchar(10) DEFAULT 'no',
  `meta_title` text NOT NULL,
  `meta_description` text NOT NULL,
  `meta_keyword` text NOT NULL,
  `feature_image` text NOT NULL,
  `publish_date` date DEFAULT NULL,
  `publish` varchar(10) DEFAULT '0',
  `sidebar` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `front_cms_settings`
--

DROP TABLE IF EXISTS `front_cms_settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `front_cms_settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `theme` varchar(50) DEFAULT NULL,
  `is_active_rtl` int(11) DEFAULT 0,
  `is_active_front_cms` int(11) DEFAULT 0,
  `is_active_sidebar` int(11) DEFAULT 0,
  `logo` varchar(200) DEFAULT NULL,
  `contact_us_email` varchar(100) DEFAULT NULL,
  `complain_form_email` varchar(100) DEFAULT NULL,
  `sidebar_options` text NOT NULL,
  `whatsapp_url` varchar(255) NOT NULL,
  `fb_url` varchar(200) NOT NULL,
  `twitter_url` varchar(200) NOT NULL,
  `youtube_url` varchar(200) NOT NULL,
  `google_plus` varchar(200) NOT NULL,
  `instagram_url` varchar(200) NOT NULL,
  `pinterest_url` varchar(200) NOT NULL,
  `linkedin_url` varchar(200) NOT NULL,
  `google_analytics` text DEFAULT NULL,
  `footer_text` varchar(500) DEFAULT NULL,
  `cookie_consent` varchar(255) NOT NULL,
  `fav_icon` varchar(250) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `gateway_ins`
--

DROP TABLE IF EXISTS `gateway_ins`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `gateway_ins` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `online_admission_id` int(11) DEFAULT NULL,
  `gateway_name` varchar(50) NOT NULL,
  `module` varchar(50) NOT NULL,
  `module_type` varchar(255) NOT NULL,
  `unique_id` varchar(255) NOT NULL,
  `parameter_details` mediumtext NOT NULL,
  `payment_status` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `online_admission_id` (`online_admission_id`),
  CONSTRAINT `gateway_ins_ibfk_1` FOREIGN KEY (`online_admission_id`) REFERENCES `online_admissions` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=145 DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `gateway_ins_response`
--

DROP TABLE IF EXISTS `gateway_ins_response`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `gateway_ins_response` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `gateway_ins_id` int(11) DEFAULT NULL,
  `posted_data` text DEFAULT NULL,
  `response` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `gateway_ins_id` (`gateway_ins_id`),
  CONSTRAINT `gateway_ins_response_ibfk_1` FOREIGN KEY (`gateway_ins_id`) REFERENCES `gateway_ins` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=39 DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `general_calls`
--

DROP TABLE IF EXISTS `general_calls`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `general_calls` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `contact` varchar(12) NOT NULL,
  `date` date NOT NULL,
  `description` varchar(500) NOT NULL,
  `follow_up_date` date NOT NULL,
  `call_duration` varchar(50) NOT NULL,
  `note` text NOT NULL,
  `call_type` varchar(20) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `grades`
--

DROP TABLE IF EXISTS `grades`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `grades` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `exam_type` varchar(250) DEFAULT NULL,
  `name` varchar(100) DEFAULT NULL,
  `point` float(10,1) DEFAULT NULL,
  `mark_from` float(10,2) DEFAULT NULL,
  `mark_upto` float(10,2) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `is_active` varchar(255) DEFAULT 'no',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `hall_approval_config`
--

DROP TABLE IF EXISTS `hall_approval_config`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `hall_approval_config` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `approver_type` enum('role','staff') NOT NULL,
  `role_id` int(11) DEFAULT NULL,
  `staff_id` int(11) DEFAULT NULL,
  `hall_id` int(11) DEFAULT NULL,
  `can_approve` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_hall_approval_config_hall_id` (`hall_id`),
  KEY `fk_hall_approval_config_role_id` (`role_id`),
  KEY `fk_hall_approval_config_staff_id` (`staff_id`),
  CONSTRAINT `fk_hall_approval_config_hall_id` FOREIGN KEY (`hall_id`) REFERENCES `halls` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_hall_approval_config_role_id` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_hall_approval_config_staff_id` FOREIGN KEY (`staff_id`) REFERENCES `staff` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `hall_bookings`
--

DROP TABLE IF EXISTS `hall_bookings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `hall_bookings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `hall_id` int(11) NOT NULL,
  `booked_by_user_id` int(11) NOT NULL,
  `purpose` varchar(255) NOT NULL,
  `start_time` datetime NOT NULL,
  `end_time` datetime NOT NULL,
  `status` enum('pending','approved','rejected','cancelled') NOT NULL DEFAULT 'pending',
  `remarks` text DEFAULT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_hall_bookings_hall_id` (`hall_id`),
  KEY `fk_hall_bookings_booked_by_user_id` (`booked_by_user_id`),
  CONSTRAINT `fk_hall_bookings_booked_by_user_id` FOREIGN KEY (`booked_by_user_id`) REFERENCES `staff` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `fk_hall_bookings_hall_id` FOREIGN KEY (`hall_id`) REFERENCES `halls` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `halls`
--

DROP TABLE IF EXISTS `halls`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `halls` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `capacity` int(11) NOT NULL,
  `location` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `available_equipment` text DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `hourly_rate` decimal(10,2) DEFAULT NULL,
  `min_booking_duration` int(11) DEFAULT 1,
  `max_booking_duration` int(11) DEFAULT NULL,
  `opening_time` time NOT NULL DEFAULT '08:00:00',
  `closing_time` time NOT NULL DEFAULT '18:00:00',
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `holiday_type`
--

DROP TABLE IF EXISTS `holiday_type`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `holiday_type` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `type` varchar(255) DEFAULT NULL,
  `is_default` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `homework`
--

DROP TABLE IF EXISTS `homework`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `homework` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `class_id` int(11) NOT NULL,
  `section_id` int(11) NOT NULL,
  `session_id` int(11) NOT NULL,
  `staff_id` int(11) NOT NULL,
  `subject_group_subject_id` int(11) DEFAULT NULL,
  `subject_id` int(11) DEFAULT NULL,
  `homework_date` date NOT NULL,
  `submit_date` date NOT NULL,
  `marks` float(10,2) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `create_date` date NOT NULL,
  `evaluation_date` date DEFAULT NULL,
  `document` varchar(200) DEFAULT NULL,
  `created_by` int(11) NOT NULL,
  `evaluated_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `subject_group_subject_id` (`subject_group_subject_id`),
  KEY `class_id` (`class_id`),
  KEY `section_id` (`section_id`),
  KEY `session_id` (`session_id`),
  KEY `staff_id` (`staff_id`),
  KEY `subject_id` (`subject_id`),
  KEY `evaluated_by` (`evaluated_by`),
  KEY `created_by` (`created_by`),
  CONSTRAINT `homework_ibfk_1` FOREIGN KEY (`subject_group_subject_id`) REFERENCES `subject_group_subjects` (`id`) ON DELETE CASCADE,
  CONSTRAINT `homework_ibfk_2` FOREIGN KEY (`class_id`) REFERENCES `classes` (`id`) ON DELETE CASCADE,
  CONSTRAINT `homework_ibfk_3` FOREIGN KEY (`section_id`) REFERENCES `sections` (`id`) ON DELETE CASCADE,
  CONSTRAINT `homework_ibfk_4` FOREIGN KEY (`session_id`) REFERENCES `sessions` (`id`) ON DELETE CASCADE,
  CONSTRAINT `homework_ibfk_5` FOREIGN KEY (`staff_id`) REFERENCES `staff` (`id`) ON DELETE CASCADE,
  CONSTRAINT `homework_ibfk_6` FOREIGN KEY (`subject_id`) REFERENCES `subjects` (`id`) ON DELETE CASCADE,
  CONSTRAINT `homework_ibfk_7` FOREIGN KEY (`evaluated_by`) REFERENCES `staff` (`id`) ON DELETE CASCADE,
  CONSTRAINT `homework_ibfk_8` FOREIGN KEY (`created_by`) REFERENCES `staff` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `homework_evaluation`
--

DROP TABLE IF EXISTS `homework_evaluation`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `homework_evaluation` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `homework_id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `student_session_id` int(11) DEFAULT NULL,
  `marks` float(10,2) DEFAULT NULL,
  `note` varchar(255) NOT NULL,
  `date` date NOT NULL,
  `status` varchar(100) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `homework_id` (`homework_id`),
  KEY `student_id` (`student_id`),
  KEY `student_session_id` (`student_session_id`),
  CONSTRAINT `homework_evaluation_ibfk_1` FOREIGN KEY (`homework_id`) REFERENCES `homework` (`id`) ON DELETE CASCADE,
  CONSTRAINT `homework_evaluation_ibfk_2` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE,
  CONSTRAINT `homework_evaluation_ibfk_3` FOREIGN KEY (`student_session_id`) REFERENCES `student_session` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `hostel`
--

DROP TABLE IF EXISTS `hostel`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `hostel` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `hostel_name` varchar(100) DEFAULT NULL,
  `type` varchar(50) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `intake` int(11) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `is_active` varchar(255) DEFAULT 'no',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `hostel_rooms`
--

DROP TABLE IF EXISTS `hostel_rooms`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `hostel_rooms` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `hostel_id` int(11) DEFAULT NULL,
  `room_type_id` int(11) DEFAULT NULL,
  `room_no` varchar(200) DEFAULT NULL,
  `no_of_bed` int(11) DEFAULT NULL,
  `cost_per_bed` float(10,2) DEFAULT 0.00,
  `title` varchar(200) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `hostel_id` (`hostel_id`),
  KEY `room_type_id` (`room_type_id`),
  CONSTRAINT `hostel_rooms_ibfk_1` FOREIGN KEY (`hostel_id`) REFERENCES `hostel` (`id`) ON DELETE CASCADE,
  CONSTRAINT `hostel_rooms_ibfk_2` FOREIGN KEY (`room_type_id`) REFERENCES `room_types` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=194 DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `id_card`
--

DROP TABLE IF EXISTS `id_card`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `id_card` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(100) NOT NULL,
  `school_name` varchar(100) NOT NULL,
  `school_address` varchar(500) NOT NULL,
  `background` varchar(100) NOT NULL,
  `logo` varchar(100) NOT NULL,
  `sign_image` varchar(100) NOT NULL,
  `enable_vertical_card` int(11) NOT NULL DEFAULT 0,
  `header_color` varchar(100) NOT NULL,
  `enable_admission_no` tinyint(1) NOT NULL COMMENT '0=disable,1=enable',
  `enable_student_name` tinyint(1) NOT NULL COMMENT '0=disable,1=enable',
  `enable_class` tinyint(1) NOT NULL COMMENT '0=disable,1=enable',
  `enable_fathers_name` tinyint(1) NOT NULL COMMENT '0=disable,1=enable',
  `enable_mothers_name` tinyint(1) NOT NULL COMMENT '0=disable,1=enable',
  `enable_address` tinyint(1) NOT NULL COMMENT '0=disable,1=enable',
  `enable_phone` tinyint(1) NOT NULL COMMENT '0=disable,1=enable',
  `enable_dob` tinyint(1) NOT NULL COMMENT '0=disable,1=enable',
  `enable_blood_group` tinyint(1) NOT NULL COMMENT '0=disable,1=enable',
  `enable_student_barcode` tinyint(4) NOT NULL DEFAULT 1 COMMENT '0=disable,1=enable',
  `enable_student_rollno` tinyint(1) NOT NULL COMMENT '0=disable,1=enable	',
  `enable_student_house_name` tinyint(1) NOT NULL DEFAULT 0 COMMENT '0=disable,1=enable	',
  `status` tinyint(1) NOT NULL COMMENT '0=disable,1=enable',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `incidental_fee_assignments`
--

DROP TABLE IF EXISTS `incidental_fee_assignments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `incidental_fee_assignments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `incidental_fee_type_id` int(11) NOT NULL COMMENT 'Foreign key to incidental_fee_types table',
  `session_id` int(11) NOT NULL COMMENT 'Foreign key to the academic sessions table',
  `student_id` int(11) DEFAULT NULL COMMENT 'Foreign key to the students table (NULL if assigned to a class)',
  `class_id` int(11) DEFAULT NULL COMMENT 'Foreign key to the classes table (NULL if assigned to a specific \r\n      student)',
  `amount_due` decimal(10,2) NOT NULL COMMENT 'The specific amount due for this assignment',
  `due_date` date DEFAULT NULL COMMENT 'Optional due date for the assigned fee',
  `status` varchar(50) NOT NULL DEFAULT 'assigned' COMMENT 'e.g., assigned, paid, partially_paid, \r\n      waived',
  `assigned_at` datetime DEFAULT current_timestamp(),
  `assigned_by` int(11) DEFAULT NULL COMMENT 'ID of the user who made this assignment',
  PRIMARY KEY (`id`),
  KEY `incidental_fee_type_id` (`incidental_fee_type_id`),
  KEY `session_id` (`session_id`),
  KEY `idx_assignment_student_session` (`student_id`,`session_id`),
  KEY `idx_assignment_class_session` (`class_id`,`session_id`),
  CONSTRAINT `incidental_fee_assignments_ibfk_1` FOREIGN KEY (`incidental_fee_type_id`) REFERENCES `incidental_fee_types` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `incidental_fee_assignments_ibfk_2` FOREIGN KEY (`session_id`) REFERENCES `sessions` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `incidental_fee_assignments_ibfk_3` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `incidental_fee_assignments_ibfk_4` FOREIGN KEY (`class_id`) REFERENCES `classes` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2265 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `incidental_fee_collections`
--

DROP TABLE IF EXISTS `incidental_fee_collections`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `incidental_fee_collections` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `incidental_fee_type_id` int(11) NOT NULL COMMENT 'Foreign key to incidental_fee_types table',
  `incidental_fee_assignment_id` int(11) DEFAULT NULL COMMENT 'Foreign key to incidental_fee_assignments table \r\n      (NULL for ad-hoc collections)',
  `session_id` int(11) NOT NULL COMMENT 'Foreign key to the academic sessions table',
  `student_id` int(11) DEFAULT NULL,
  `non_student_name` varchar(255) DEFAULT NULL,
  `amount_collected` decimal(10,2) NOT NULL COMMENT 'The actual amount collected',
  `bill_date` date DEFAULT NULL,
  `date_collected` datetime DEFAULT current_timestamp(),
  `collected_by` int(11) DEFAULT NULL COMMENT 'ID of the user who collected the fee',
  `receipt_no` varchar(100) NOT NULL COMMENT 'Unique receipt number for the collection',
  `notes` text DEFAULT NULL COMMENT 'Any additional notes for the collection',
  `application_ref_no` varchar(100) DEFAULT NULL COMMENT 'Application reference number (required for APPLICATION FEE, TUITION FEE, Other fee)',
  `payment_mode` varchar(50) DEFAULT NULL,
  `txn_id` varchar(255) DEFAULT NULL,
  `bank_name` varchar(255) DEFAULT NULL,
  `cheque_no` varchar(100) DEFAULT NULL,
  `cheque_date` date DEFAULT NULL,
  `received_by` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `receipt_no` (`receipt_no`),
  KEY `incidental_fee_type_id` (`incidental_fee_type_id`),
  KEY `incidental_fee_assignment_id` (`incidental_fee_assignment_id`),
  KEY `session_id` (`session_id`),
  KEY `idx_collection_student_session` (`student_id`,`session_id`),
  KEY `idx_collection_receipt` (`receipt_no`),
  KEY `idx_application_ref_no` (`application_ref_no`),
  CONSTRAINT `incidental_fee_collections_ibfk_1` FOREIGN KEY (`incidental_fee_type_id`) REFERENCES `incidental_fee_types` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `incidental_fee_collections_ibfk_2` FOREIGN KEY (`incidental_fee_assignment_id`) REFERENCES `incidental_fee_assignments` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `incidental_fee_collections_ibfk_3` FOREIGN KEY (`session_id`) REFERENCES `sessions` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `incidental_fee_collections_ibfk_4` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=722 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `incidental_fee_types`
--

DROP TABLE IF EXISTS `incidental_fee_types`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `incidental_fee_types` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL COMMENT 'Title of the fee type (e.g., Paid Exam Fee, Library Fine)',
  `description` text DEFAULT NULL COMMENT 'Detailed description of the fee type',
  `default_amount` decimal(10,2) DEFAULT NULL COMMENT 'Suggested default amount for this fee type, can be NULL\r\n      for variable amounts',
  `is_assignable` tinyint(1) NOT NULL DEFAULT 0 COMMENT '0 for ad-hoc (entry basis), 1 if can be \r\n      assigned to students/classes',
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `created_by` int(11) DEFAULT NULL COMMENT 'ID of the user who created this fee type',
  PRIMARY KEY (`id`),
  UNIQUE KEY `title` (`title`)
) ENGINE=InnoDB AUTO_INCREMENT=19 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `income`
--

DROP TABLE IF EXISTS `income`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `income` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `income_head_id` int(11) DEFAULT NULL,
  `name` varchar(50) DEFAULT NULL,
  `invoice_no` varchar(200) DEFAULT NULL,
  `date` date DEFAULT NULL,
  `amount` float(10,2) DEFAULT 0.00,
  `note` text DEFAULT NULL,
  `is_active` varchar(255) DEFAULT 'yes',
  `documents` varchar(255) DEFAULT NULL,
  `is_deleted` varchar(255) DEFAULT 'no',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `income_head_id` (`income_head_id`),
  CONSTRAINT `income_ibfk_1` FOREIGN KEY (`income_head_id`) REFERENCES `income_head` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `income_head`
--

DROP TABLE IF EXISTS `income_head`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `income_head` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `income_category` varchar(255) DEFAULT NULL,
  `description` varchar(255) DEFAULT NULL,
  `is_active` varchar(255) NOT NULL DEFAULT 'yes',
  `is_deleted` varchar(255) NOT NULL DEFAULT 'no',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `inv_asset_assignments`
--

DROP TABLE IF EXISTS `inv_asset_assignments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `inv_asset_assignments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `asset_id` int(11) NOT NULL,
  `assignee_type` varchar(30) NOT NULL,
  `assignee_id` int(11) NOT NULL,
  `assigned_on` date NOT NULL,
  `assigned_by` int(11) DEFAULT NULL,
  `returned_on` date DEFAULT NULL,
  `return_note` text DEFAULT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'assigned',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_inv_asset_assignments_asset_id` (`asset_id`),
  KEY `idx_inv_asset_assignments_assignee` (`assignee_type`,`assignee_id`),
  KEY `idx_inv_asset_assignments_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `inv_asset_locations`
--

DROP TABLE IF EXISTS `inv_asset_locations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `inv_asset_locations` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `location_code` varchar(50) NOT NULL,
  `location_name` varchar(255) NOT NULL,
  `location_type` varchar(30) NOT NULL DEFAULT 'room',
  `department_id` int(11) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_inv_asset_location_code` (`location_code`),
  KEY `idx_inv_asset_locations_type` (`location_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `inv_asset_maintenance_logs`
--

DROP TABLE IF EXISTS `inv_asset_maintenance_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `inv_asset_maintenance_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `asset_id` int(11) NOT NULL,
  `maintenance_type` varchar(30) NOT NULL DEFAULT 'breakdown',
  `vendor_name` varchar(255) DEFAULT NULL,
  `opened_on` date NOT NULL,
  `closed_on` date DEFAULT NULL,
  `status` varchar(30) NOT NULL DEFAULT 'open',
  `issue_description` text DEFAULT NULL,
  `resolution_note` text DEFAULT NULL,
  `cost_amount` decimal(14,2) NOT NULL DEFAULT 0.00,
  `next_due_date` date DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_inv_asset_maint_asset_id` (`asset_id`),
  KEY `idx_inv_asset_maint_status` (`status`),
  KEY `idx_inv_asset_maint_opened_on` (`opened_on`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `inv_asset_transfers`
--

DROP TABLE IF EXISTS `inv_asset_transfers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `inv_asset_transfers` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `asset_id` int(11) NOT NULL,
  `from_location_id` int(11) DEFAULT NULL,
  `to_location_id` int(11) DEFAULT NULL,
  `from_assignee_type` varchar(30) DEFAULT NULL,
  `from_assignee_id` int(11) DEFAULT NULL,
  `to_assignee_type` varchar(30) DEFAULT NULL,
  `to_assignee_id` int(11) DEFAULT NULL,
  `transfer_date` date NOT NULL,
  `transferred_by` int(11) DEFAULT NULL,
  `approved_by` int(11) DEFAULT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'completed',
  `remarks` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_inv_asset_transfers_asset_id` (`asset_id`),
  KEY `idx_inv_asset_transfers_transfer_date` (`transfer_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `inv_assets`
--

DROP TABLE IF EXISTS `inv_assets`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `inv_assets` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `asset_tag` varchar(100) NOT NULL,
  `asset_name` varchar(255) NOT NULL,
  `item_id` int(11) DEFAULT NULL,
  `item_stock_id` int(11) DEFAULT NULL,
  `serial_no` varchar(100) DEFAULT NULL,
  `model_no` varchar(100) DEFAULT NULL,
  `brand_name` varchar(100) DEFAULT NULL,
  `category_id` int(11) DEFAULT NULL,
  `supplier_id` int(11) DEFAULT NULL,
  `purchase_order_id` int(11) DEFAULT NULL,
  `purchase_date` date DEFAULT NULL,
  `purchase_cost` decimal(14,2) NOT NULL DEFAULT 0.00,
  `capitalization_date` date DEFAULT NULL,
  `warranty_start` date DEFAULT NULL,
  `warranty_end` date DEFAULT NULL,
  `current_status` varchar(30) NOT NULL DEFAULT 'in_stock',
  `current_location_id` int(11) DEFAULT NULL,
  `assigned_to_staff_id` int(11) DEFAULT NULL,
  `assigned_to_type` varchar(30) DEFAULT NULL,
  `qr_code_value` varchar(255) DEFAULT NULL,
  `remarks` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_inv_asset_tag` (`asset_tag`),
  KEY `idx_inv_assets_status` (`current_status`),
  KEY `idx_inv_assets_item_id` (`item_id`),
  KEY `idx_inv_assets_location` (`current_location_id`),
  KEY `idx_inv_assets_assigned_staff` (`assigned_to_staff_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `inv_goods_receipt_items`
--

DROP TABLE IF EXISTS `inv_goods_receipt_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `inv_goods_receipt_items` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `grn_id` int(11) NOT NULL,
  `po_item_id` int(11) DEFAULT NULL,
  `item_id` int(11) DEFAULT NULL,
  `received_qty` decimal(14,2) NOT NULL DEFAULT 0.00,
  `accepted_qty` decimal(14,2) NOT NULL DEFAULT 0.00,
  `rejected_qty` decimal(14,2) NOT NULL DEFAULT 0.00,
  `unit_cost` decimal(14,2) NOT NULL DEFAULT 0.00,
  `line_total` decimal(14,2) NOT NULL DEFAULT 0.00,
  `qc_status` varchar(30) NOT NULL DEFAULT 'accepted',
  `remarks` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_inv_grn_items_grn_id` (`grn_id`),
  KEY `idx_inv_grn_items_item_id` (`item_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `inv_goods_receipts`
--

DROP TABLE IF EXISTS `inv_goods_receipts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `inv_goods_receipts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `grn_no` varchar(50) NOT NULL,
  `grn_date` date NOT NULL,
  `po_id` int(11) NOT NULL,
  `received_by` int(11) NOT NULL,
  `store_id` int(11) DEFAULT NULL,
  `invoice_no` varchar(100) DEFAULT NULL,
  `invoice_date` date DEFAULT NULL,
  `status` varchar(30) NOT NULL DEFAULT 'draft',
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_inv_grn_no` (`grn_no`),
  KEY `idx_inv_grn_po_id` (`po_id`),
  KEY `idx_inv_grn_date` (`grn_date`),
  KEY `idx_inv_grn_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `inv_indent_approvals`
--

DROP TABLE IF EXISTS `inv_indent_approvals`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `inv_indent_approvals` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `indent_id` int(11) NOT NULL,
  `approver_staff_id` int(11) NOT NULL,
  `approval_level` int(11) NOT NULL DEFAULT 1,
  `decision` varchar(20) NOT NULL DEFAULT 'pending',
  `decision_date` datetime DEFAULT NULL,
  `comments` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_inv_indent_approvals_indent_id` (`indent_id`),
  KEY `idx_inv_indent_approvals_approver` (`approver_staff_id`),
  KEY `idx_inv_indent_approvals_decision` (`decision`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `inv_indent_items`
--

DROP TABLE IF EXISTS `inv_indent_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `inv_indent_items` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `indent_id` int(11) NOT NULL,
  `item_category_id` int(11) DEFAULT NULL,
  `item_id` int(11) DEFAULT NULL,
  `item_name` varchar(255) DEFAULT NULL,
  `spec` text DEFAULT NULL,
  `quantity` decimal(14,2) NOT NULL DEFAULT 0.00,
  `uom` varchar(50) DEFAULT NULL,
  `estimated_unit_cost` decimal(14,2) NOT NULL DEFAULT 0.00,
  `estimated_total_cost` decimal(14,2) NOT NULL DEFAULT 0.00,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_inv_indent_items_indent_id` (`indent_id`),
  KEY `idx_inv_indent_items_item_id` (`item_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `inv_indents`
--

DROP TABLE IF EXISTS `inv_indents`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `inv_indents` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `indent_no` varchar(50) NOT NULL,
  `request_date` date NOT NULL,
  `required_by_date` date DEFAULT NULL,
  `requested_by` int(11) NOT NULL,
  `department_id` int(11) DEFAULT NULL,
  `priority` varchar(20) NOT NULL DEFAULT 'normal',
  `status` varchar(30) NOT NULL DEFAULT 'draft',
  `remarks` text DEFAULT NULL,
  `total_estimated_cost` decimal(14,2) NOT NULL DEFAULT 0.00,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_inv_indent_no` (`indent_no`),
  KEY `idx_inv_indents_status` (`status`),
  KEY `idx_inv_indents_requested_by` (`requested_by`),
  KEY `idx_inv_indents_request_date` (`request_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `inv_po_approval_rules`
--

DROP TABLE IF EXISTS `inv_po_approval_rules`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `inv_po_approval_rules` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `rule_name` varchar(150) NOT NULL,
  `department_id` int(11) DEFAULT NULL,
  `min_amount` decimal(14,2) NOT NULL DEFAULT 0.00,
  `max_amount` decimal(14,2) DEFAULT NULL,
  `approval_level` int(11) NOT NULL,
  `approver_type` varchar(20) NOT NULL DEFAULT 'staff',
  `approver_staff_id` int(11) DEFAULT NULL,
  `approver_role_id` int(11) DEFAULT NULL,
  `sort_order` int(11) NOT NULL DEFAULT 1,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_inv_po_rules_department` (`department_id`),
  KEY `idx_inv_po_rules_amount` (`min_amount`,`max_amount`),
  KEY `idx_inv_po_rules_level` (`approval_level`),
  KEY `idx_inv_po_rules_active` (`is_active`),
  KEY `idx_inv_po_rules_staff` (`approver_staff_id`),
  KEY `idx_inv_po_rules_role` (`approver_role_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `inv_po_approvals`
--

DROP TABLE IF EXISTS `inv_po_approvals`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `inv_po_approvals` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `po_id` int(11) NOT NULL,
  `approval_level` int(11) NOT NULL DEFAULT 1,
  `approver_staff_id` int(11) NOT NULL,
  `decision` varchar(20) NOT NULL DEFAULT 'pending',
  `decision_date` datetime DEFAULT NULL,
  `comments` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_inv_po_approvals_po_id` (`po_id`),
  KEY `idx_inv_po_approvals_approver` (`approver_staff_id`),
  KEY `idx_inv_po_approvals_decision` (`decision`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `inv_purchase_order_items`
--

DROP TABLE IF EXISTS `inv_purchase_order_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `inv_purchase_order_items` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `po_id` int(11) NOT NULL,
  `indent_item_id` int(11) DEFAULT NULL,
  `item_id` int(11) DEFAULT NULL,
  `item_name` varchar(255) DEFAULT NULL,
  `quantity` decimal(14,2) NOT NULL DEFAULT 0.00,
  `uom` varchar(50) DEFAULT NULL,
  `unit_price` decimal(14,2) NOT NULL DEFAULT 0.00,
  `tax_percent` decimal(6,2) NOT NULL DEFAULT 0.00,
  `tax_amount` decimal(14,2) NOT NULL DEFAULT 0.00,
  `line_total` decimal(14,2) NOT NULL DEFAULT 0.00,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_inv_po_items_po_id` (`po_id`),
  KEY `idx_inv_po_items_item_id` (`item_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `inv_purchase_orders`
--

DROP TABLE IF EXISTS `inv_purchase_orders`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `inv_purchase_orders` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `po_no` varchar(50) NOT NULL,
  `po_date` date NOT NULL,
  `indent_id` int(11) DEFAULT NULL,
  `supplier_id` int(11) DEFAULT NULL,
  `status` varchar(30) NOT NULL DEFAULT 'draft',
  `subtotal` decimal(14,2) NOT NULL DEFAULT 0.00,
  `discount_amount` decimal(14,2) NOT NULL DEFAULT 0.00,
  `tax_amount` decimal(14,2) NOT NULL DEFAULT 0.00,
  `total_amount` decimal(14,2) NOT NULL DEFAULT 0.00,
  `expected_delivery_date` date DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `approved_by` int(11) DEFAULT NULL,
  `approved_at` datetime DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_inv_po_no` (`po_no`),
  KEY `idx_inv_po_status` (`status`),
  KEY `idx_inv_po_supplier` (`supplier_id`),
  KEY `idx_inv_po_date` (`po_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `item`
--

DROP TABLE IF EXISTS `item`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `item` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `item_category_id` int(11) DEFAULT NULL,
  `item_store_id` int(11) DEFAULT NULL,
  `item_supplier_id` int(11) DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `unit` varchar(100) NOT NULL,
  `item_photo` varchar(225) DEFAULT NULL,
  `description` text NOT NULL,
  `quantity` int(11) NOT NULL,
  `date` date DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `item_category_id` (`item_category_id`),
  KEY `item_store_id` (`item_store_id`),
  KEY `item_supplier_id` (`item_supplier_id`),
  CONSTRAINT `item_ibfk_1` FOREIGN KEY (`item_category_id`) REFERENCES `item_category` (`id`) ON DELETE CASCADE,
  CONSTRAINT `item_ibfk_2` FOREIGN KEY (`item_store_id`) REFERENCES `item_store` (`id`) ON DELETE CASCADE,
  CONSTRAINT `item_ibfk_3` FOREIGN KEY (`item_supplier_id`) REFERENCES `item_supplier` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `item_category`
--

DROP TABLE IF EXISTS `item_category`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `item_category` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `item_category` varchar(255) NOT NULL,
  `is_asset` tinyint(1) NOT NULL DEFAULT 0,
  `asset_tracking_mode` varchar(20) NOT NULL DEFAULT 'bulk',
  `is_active` varchar(255) NOT NULL DEFAULT 'yes',
  `description` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `item_issue`
--

DROP TABLE IF EXISTS `item_issue`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `item_issue` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `issue_type` varchar(15) DEFAULT NULL,
  `issue_target_type` varchar(20) NOT NULL DEFAULT 'staff',
  `issue_location_type` varchar(60) DEFAULT NULL,
  `issue_place_name` varchar(120) DEFAULT NULL,
  `issue_floor` varchar(30) DEFAULT NULL,
  `issue_room_no` varchar(40) DEFAULT NULL,
  `issue_block` varchar(60) DEFAULT NULL,
  `issue_building` varchar(120) DEFAULT NULL,
  `issue_location_note` varchar(255) DEFAULT NULL,
  `issue_to` int(11) NOT NULL,
  `issue_by` int(11) DEFAULT NULL,
  `issue_date` date DEFAULT NULL,
  `return_date` date DEFAULT NULL,
  `item_category_id` int(11) DEFAULT NULL,
  `item_id` int(11) DEFAULT NULL,
  `quantity` int(11) NOT NULL,
  `note` text NOT NULL,
  `is_returned` int(11) NOT NULL DEFAULT 1,
  `is_active` varchar(10) DEFAULT 'no',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `item_id` (`item_id`),
  KEY `item_category_id` (`item_category_id`),
  KEY `issue_to` (`issue_to`),
  KEY `issue_by` (`issue_by`),
  CONSTRAINT `item_issue_ibfk_1` FOREIGN KEY (`item_id`) REFERENCES `item` (`id`) ON DELETE CASCADE,
  CONSTRAINT `item_issue_ibfk_2` FOREIGN KEY (`item_category_id`) REFERENCES `item_category` (`id`) ON DELETE CASCADE,
  CONSTRAINT `item_issue_ibfk_3` FOREIGN KEY (`issue_to`) REFERENCES `staff` (`id`) ON DELETE CASCADE,
  CONSTRAINT `item_issue_ibfk_4` FOREIGN KEY (`issue_by`) REFERENCES `staff` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `item_stock`
--

DROP TABLE IF EXISTS `item_stock`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `item_stock` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `item_id` int(11) DEFAULT NULL,
  `supplier_id` int(11) DEFAULT NULL,
  `batch_no` varchar(80) DEFAULT NULL,
  `store_id` int(11) DEFAULT NULL,
  `symbol` varchar(10) NOT NULL DEFAULT '+',
  `quantity` int(11) DEFAULT NULL,
  `purchase_price` float(10,2) NOT NULL,
  `date` date NOT NULL,
  `manufacturing_date` date DEFAULT NULL,
  `expiry_date` date DEFAULT NULL,
  `warranty_upto` date DEFAULT NULL,
  `license_key` varchar(255) DEFAULT NULL,
  `license_valid_from` date DEFAULT NULL,
  `license_valid_till` date DEFAULT NULL,
  `attachment` varchar(250) DEFAULT NULL,
  `description` text NOT NULL,
  `is_active` varchar(10) DEFAULT 'yes',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `item_id` (`item_id`),
  KEY `supplier_id` (`supplier_id`),
  KEY `store_id` (`store_id`),
  CONSTRAINT `item_stock_ibfk_1` FOREIGN KEY (`item_id`) REFERENCES `item` (`id`) ON DELETE CASCADE,
  CONSTRAINT `item_stock_ibfk_2` FOREIGN KEY (`supplier_id`) REFERENCES `item_supplier` (`id`) ON DELETE CASCADE,
  CONSTRAINT `item_stock_ibfk_3` FOREIGN KEY (`store_id`) REFERENCES `item_store` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `item_store`
--

DROP TABLE IF EXISTS `item_store`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `item_store` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `item_store` varchar(255) NOT NULL,
  `code` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `item_supplier`
--

DROP TABLE IF EXISTS `item_supplier`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `item_supplier` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `item_supplier` varchar(255) NOT NULL,
  `phone` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `address` varchar(255) NOT NULL,
  `contact_person_name` varchar(255) NOT NULL,
  `contact_person_phone` varchar(255) NOT NULL,
  `contact_person_email` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `languages`
--

DROP TABLE IF EXISTS `languages`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `languages` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `language` varchar(50) DEFAULT NULL,
  `short_code` varchar(255) NOT NULL,
  `country_code` varchar(255) NOT NULL,
  `is_rtl` int(11) NOT NULL,
  `is_deleted` varchar(10) NOT NULL DEFAULT 'yes',
  `is_active` varchar(255) DEFAULT 'no',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=95 DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `lead_api_vendors`
--

DROP TABLE IF EXISTS `lead_api_vendors`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `lead_api_vendors` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `vendor_code` varchar(50) NOT NULL,
  `vendor_name` varchar(100) NOT NULL,
  `api_key_hash` varchar(255) NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_by` int(11) NOT NULL DEFAULT 1,
  `last_used_at` datetime DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_vendor_code` (`vendor_code`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `leave_substitutions`
--

DROP TABLE IF EXISTS `leave_substitutions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `leave_substitutions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `leave_request_id` int(11) NOT NULL,
  `substitute_staff_id` int(11) NOT NULL,
  `date` date NOT NULL,
  `period` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `leave_types`
--

DROP TABLE IF EXISTS `leave_types`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `leave_types` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `type` varchar(200) NOT NULL,
  `is_active` varchar(50) NOT NULL,
  `is_staff_specific` enum('All','Student','Staff') NOT NULL DEFAULT 'All',
  `max_leave_days` int(11) NOT NULL DEFAULT 0,
  `is_lop` tinyint(1) NOT NULL DEFAULT 0,
  `requires_balance_check` tinyint(1) NOT NULL DEFAULT 1,
  `is_carry_forward` tinyint(1) NOT NULL DEFAULT 0,
  `max_carry_forward` int(11) DEFAULT 0,
  `gender_specific` varchar(20) DEFAULT NULL,
  `leave_encashment` tinyint(1) NOT NULL DEFAULT 0,
  `credit_source_type_id` int(11) DEFAULT NULL,
  `strict_day_lock` tinyint(1) NOT NULL DEFAULT 0 COMMENT 'If 1, approved leaves of this type lock the day in staff_day_status, overriding biometric attendance for payroll.',
  PRIMARY KEY (`id`),
  UNIQUE KEY `type` (`type`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `lesson`
--

DROP TABLE IF EXISTS `lesson`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `lesson` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `session_id` int(11) NOT NULL,
  `subject_group_subject_id` int(11) NOT NULL,
  `subject_group_class_sections_id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `session_id` (`session_id`),
  KEY `subject_group_subject_id` (`subject_group_subject_id`),
  KEY `subject_group_class_sections_id` (`subject_group_class_sections_id`),
  CONSTRAINT `lesson_ibfk_1` FOREIGN KEY (`session_id`) REFERENCES `sessions` (`id`) ON DELETE CASCADE,
  CONSTRAINT `lesson_ibfk_2` FOREIGN KEY (`subject_group_subject_id`) REFERENCES `subject_group_subjects` (`id`) ON DELETE CASCADE,
  CONSTRAINT `lesson_ibfk_3` FOREIGN KEY (`subject_group_class_sections_id`) REFERENCES `subject_group_class_sections` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `lesson_plan_forum`
--

DROP TABLE IF EXISTS `lesson_plan_forum`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `lesson_plan_forum` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `subject_syllabus_id` int(11) NOT NULL,
  `type` varchar(20) NOT NULL COMMENT 'staff,student',
  `staff_id` int(11) DEFAULT NULL,
  `student_id` int(11) DEFAULT NULL,
  `message` text NOT NULL,
  `created_date` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `subject_syllabus_id` (`subject_syllabus_id`),
  KEY `student_id` (`student_id`),
  KEY `staff_id` (`staff_id`),
  CONSTRAINT `lesson_plan_forum_ibfk_1` FOREIGN KEY (`subject_syllabus_id`) REFERENCES `subject_syllabus` (`id`) ON DELETE CASCADE,
  CONSTRAINT `lesson_plan_forum_ibfk_2` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE,
  CONSTRAINT `lesson_plan_forum_ibfk_3` FOREIGN KEY (`staff_id`) REFERENCES `staff` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `libarary_members`
--

DROP TABLE IF EXISTS `libarary_members`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `libarary_members` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `library_card_no` varchar(50) DEFAULT NULL,
  `member_type` varchar(50) DEFAULT NULL,
  `member_id` int(11) DEFAULT NULL,
  `is_active` varchar(10) NOT NULL DEFAULT 'no',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1886 DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `library_attendance`
--

DROP TABLE IF EXISTS `library_attendance`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `library_attendance` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` varchar(255) NOT NULL,
  `user_type` enum('student','staff') NOT NULL,
  `name` varchar(255) NOT NULL,
  `attendance_date` date NOT NULL,
  `in_time` datetime NOT NULL,
  `out_time` datetime DEFAULT NULL,
  `duration` time DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`,`user_type`,`attendance_date`)
) ENGINE=InnoDB AUTO_INCREMENT=1242 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `library_book_types`
--

DROP TABLE IF EXISTS `library_book_types`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `library_book_types` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `book_type_name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `library_categories`
--

DROP TABLE IF EXISTS `library_categories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `library_categories` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `category_name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=18 DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `library_position_racks`
--

DROP TABLE IF EXISTS `library_position_racks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `library_position_racks` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `rack_name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=133 DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `library_position_shelves`
--

DROP TABLE IF EXISTS `library_position_shelves`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `library_position_shelves` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `shelf_name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `rack_id` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `rack_id` (`rack_id`),
  CONSTRAINT `library_position_shelves_ibfk_1` FOREIGN KEY (`rack_id`) REFERENCES `library_position_racks` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=1378 DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `library_publishers`
--

DROP TABLE IF EXISTS `library_publishers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `library_publishers` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `publisher_name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=432 DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `library_subcategories`
--

DROP TABLE IF EXISTS `library_subcategories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `library_subcategories` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `subcategory_name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `category_id` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `category_id` (`category_id`),
  CONSTRAINT `library_subcategories_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `library_categories` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=49 DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `library_subjects`
--

DROP TABLE IF EXISTS `library_subjects`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `library_subjects` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `subject_name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=32 DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `library_vendors`
--

DROP TABLE IF EXISTS `library_vendors`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `library_vendors` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `vendor_name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=432 DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `logs`
--

DROP TABLE IF EXISTS `logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `message` text DEFAULT NULL,
  `record_id` text DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `action` varchar(50) DEFAULT NULL,
  `ip_address` varchar(50) DEFAULT NULL,
  `platform` varchar(50) DEFAULT NULL,
  `agent` varchar(50) DEFAULT NULL,
  `time` timestamp NOT NULL DEFAULT current_timestamp(),
  `created_at` date DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=36966 DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `mark_divisions`
--

DROP TABLE IF EXISTS `mark_divisions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mark_divisions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(200) DEFAULT NULL,
  `percentage_from` float(10,2) DEFAULT NULL,
  `percentage_to` float(10,2) DEFAULT NULL,
  `is_active` int(11) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `messages`
--

DROP TABLE IF EXISTS `messages`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `messages` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sender_id` int(11) DEFAULT NULL,
  `title` varchar(200) DEFAULT NULL,
  `template_id` varchar(100) DEFAULT NULL,
  `email_template_id` int(11) DEFAULT NULL,
  `sms_template_id` int(11) DEFAULT NULL,
  `send_through` varchar(20) DEFAULT NULL,
  `message` text DEFAULT NULL,
  `send_mail` varchar(10) DEFAULT '0',
  `send_sms` varchar(10) DEFAULT '0',
  `is_group` varchar(10) DEFAULT '0',
  `is_individual` varchar(10) DEFAULT '0',
  `is_class` int(11) NOT NULL DEFAULT 0,
  `is_schedule` int(11) NOT NULL,
  `sent` int(11) DEFAULT NULL,
  `schedule_date_time` datetime DEFAULT NULL,
  `group_list` text DEFAULT NULL,
  `user_list` text DEFAULT NULL,
  `send_to` varchar(255) DEFAULT NULL,
  `schedule_class` int(11) DEFAULT NULL,
  `schedule_section` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `sender_staff_id` int(11) DEFAULT NULL COMMENT 'Staff ID of the person who sent the communication',
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `sender_name` varchar(255) DEFAULT NULL,
  `sender_employee_id` varchar(50) DEFAULT NULL,
  `sender_type` varchar(50) DEFAULT 'staff' COMMENT 'Type of sender: staff, admin, system, etc.',
  `sender_created_by` int(11) DEFAULT NULL COMMENT 'Staff/User ID who sent this',
  PRIMARY KEY (`id`),
  KEY `idx_sender_staff_id` (`sender_staff_id`),
  KEY `idx_created_at` (`created_at`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `meta_webhook_events`
--

DROP TABLE IF EXISTS `meta_webhook_events`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `meta_webhook_events` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `received_at` datetime NOT NULL,
  `source_ip` varchar(45) DEFAULT NULL,
  `signature_status` enum('ok','fail','skipped') NOT NULL DEFAULT 'skipped',
  `leadgen_id` varchar(64) DEFAULT NULL,
  `page_id` varchar(64) DEFAULT NULL,
  `form_id` varchar(64) DEFAULT NULL,
  `outcome` varchar(50) NOT NULL DEFAULT 'pending',
  `enquiry_id` int(11) DEFAULT NULL,
  `note` varchar(500) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_received_at` (`received_at`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `migrations`
--

DROP TABLE IF EXISTS `migrations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `migrations` (
  `version` bigint(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `monthly_leave_increment_rules`
--

DROP TABLE IF EXISTS `monthly_leave_increment_rules`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `monthly_leave_increment_rules` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `leave_type_id` int(11) NOT NULL COMMENT 'Foreign key to leave_types table',
  `increment_days` decimal(5,2) NOT NULL DEFAULT 1.00 COMMENT 'Days to increment per month',
  `enabled` tinyint(1) NOT NULL DEFAULT 1 COMMENT 'Enable/disable this rule',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_leave_type` (`leave_type_id`),
  KEY `idx_enabled` (`enabled`),
  CONSTRAINT `fk_monthly_rules_leave_type` FOREIGN KEY (`leave_type_id`) REFERENCES `leave_types` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Stores monthly leave increment rules for multiple leave types';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `multi_branch`
--

DROP TABLE IF EXISTS `multi_branch`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `multi_branch` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `branch_name` varchar(200) DEFAULT NULL,
  `branch_url` varchar(500) NOT NULL,
  `hostname` varchar(200) DEFAULT NULL,
  `username` varchar(200) DEFAULT NULL,
  `password` varchar(200) DEFAULT NULL,
  `database_name` varchar(200) DEFAULT NULL,
  `directory_path` varchar(500) DEFAULT NULL,
  `is_verified` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `naac_manual_configuration`
--

DROP TABLE IF EXISTS `naac_manual_configuration`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `naac_manual_configuration` (
  `id` int(11) NOT NULL,
  `manual_id` varchar(255) NOT NULL,
  `institution_category` int(11) NOT NULL,
  `manual_description` text NOT NULL,
  `month` int(11) NOT NULL,
  `year` int(11) NOT NULL,
  `total_criteria` int(11) NOT NULL,
  `total_key_indicators` int(11) NOT NULL,
  `total_qualitative_metrics` int(11) NOT NULL,
  `total_quantitative_metrics` int(11) NOT NULL,
  `total_metrics` int(11) NOT NULL,
  `total_weightage` int(11) NOT NULL,
  `total_marks` int(11) NOT NULL,
  `is_optional_metric` varchar(10) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `notification_roles`
--

DROP TABLE IF EXISTS `notification_roles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `notification_roles` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `send_notification_id` int(11) DEFAULT NULL,
  `role_id` int(11) DEFAULT NULL,
  `is_active` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `send_notification_id` (`send_notification_id`),
  KEY `role_id` (`role_id`),
  CONSTRAINT `notification_roles_ibfk_1` FOREIGN KEY (`send_notification_id`) REFERENCES `send_notification` (`id`) ON DELETE CASCADE,
  CONSTRAINT `notification_roles_ibfk_2` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `notification_setting`
--

DROP TABLE IF EXISTS `notification_setting`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `notification_setting` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `type` varchar(100) DEFAULT NULL,
  `is_mail` varchar(10) DEFAULT '0',
  `is_sms` varchar(10) DEFAULT '0',
  `is_notification` int(11) NOT NULL DEFAULT 0,
  `display_notification` int(11) NOT NULL DEFAULT 0,
  `display_sms` int(11) NOT NULL DEFAULT 1,
  `is_student_recipient` int(11) DEFAULT NULL,
  `is_guardian_recipient` int(11) DEFAULT NULL,
  `is_staff_recipient` int(11) DEFAULT NULL,
  `display_student_recipient` int(11) DEFAULT NULL,
  `display_guardian_recipient` int(11) DEFAULT NULL,
  `display_staff_recipient` int(11) DEFAULT NULL,
  `subject` varchar(255) NOT NULL,
  `template_id` varchar(100) NOT NULL,
  `template` longtext NOT NULL,
  `variables` text NOT NULL,
  `notification_order` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `is_whatsapp` tinyint(1) NOT NULL DEFAULT 0,
  `display_whatsapp` tinyint(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=30 DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `offline_fees_payments`
--

DROP TABLE IF EXISTS `offline_fees_payments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `offline_fees_payments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `invoice_id` varchar(50) DEFAULT NULL,
  `student_session_id` int(11) DEFAULT NULL,
  `student_fees_master_id` int(11) DEFAULT NULL,
  `fee_groups_feetype_id` int(11) DEFAULT NULL,
  `student_transport_fee_id` int(11) DEFAULT NULL,
  `payment_date` date DEFAULT NULL,
  `bank_from` varchar(200) DEFAULT NULL,
  `bank_account_transferred` varchar(200) DEFAULT NULL,
  `reference` varchar(200) DEFAULT NULL,
  `amount` float(10,2) DEFAULT NULL,
  `submit_date` datetime DEFAULT NULL,
  `approve_date` datetime DEFAULT NULL,
  `attachment` text DEFAULT NULL,
  `reply` text DEFAULT NULL,
  `approved_by` int(11) DEFAULT NULL,
  `is_active` varchar(1) DEFAULT '0',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `student_fees_master_id` (`student_fees_master_id`),
  KEY `fee_groups_feetype_id` (`fee_groups_feetype_id`),
  KEY `student_transport_fee_id` (`student_transport_fee_id`),
  KEY `offline_fees_payments_ibfk_4` (`approved_by`),
  KEY `student_session_id` (`student_session_id`),
  CONSTRAINT `offline_fees_payments_ibfk_1` FOREIGN KEY (`student_fees_master_id`) REFERENCES `student_fees_master` (`id`) ON DELETE CASCADE,
  CONSTRAINT `offline_fees_payments_ibfk_2` FOREIGN KEY (`fee_groups_feetype_id`) REFERENCES `fee_groups_feetype` (`id`) ON DELETE CASCADE,
  CONSTRAINT `offline_fees_payments_ibfk_3` FOREIGN KEY (`student_transport_fee_id`) REFERENCES `student_transport_fees` (`id`) ON DELETE CASCADE,
  CONSTRAINT `offline_fees_payments_ibfk_4` FOREIGN KEY (`approved_by`) REFERENCES `staff` (`id`) ON DELETE CASCADE,
  CONSTRAINT `offline_fees_payments_ibfk_5` FOREIGN KEY (`student_session_id`) REFERENCES `student_session` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `online_admission_courses`
--

DROP TABLE IF EXISTS `online_admission_courses`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `online_admission_courses` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `course_name` varchar(255) NOT NULL,
  `course_code` varchar(50) DEFAULT NULL,
  `course_level` varchar(10) DEFAULT NULL,
  `admission_type` varchar(20) DEFAULT NULL,
  `govt_fee` decimal(12,2) DEFAULT NULL,
  `mgt_fee` decimal(12,2) DEFAULT NULL,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `description` text DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `is_restricted` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=31 DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `online_admission_custom_field_value`
--

DROP TABLE IF EXISTS `online_admission_custom_field_value`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `online_admission_custom_field_value` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `belong_table_id` int(11) DEFAULT NULL,
  `custom_field_id` int(11) DEFAULT NULL,
  `field_value` longtext NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `custom_field_id` (`custom_field_id`),
  KEY `idx_belong_table_id` (`belong_table_id`),
  KEY `idx_field_value` (`field_value`(200)),
  CONSTRAINT `online_admission_custom_field_value_ibfk_1` FOREIGN KEY (`custom_field_id`) REFERENCES `custom_fields` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `online_admission_fields`
--

DROP TABLE IF EXISTS `online_admission_fields`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `online_admission_fields` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(250) DEFAULT NULL,
  `status` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=42 DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `online_admission_lateral_details`
--

DROP TABLE IF EXISTS `online_admission_lateral_details`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `online_admission_lateral_details` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `online_admission_id` int(11) NOT NULL,
  `lateral_course_id` varchar(255) DEFAULT NULL,
  `pre_final_sem_subjects` text DEFAULT NULL,
  `final_sem_subjects` text DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `online_admission_nata_details`
--

DROP TABLE IF EXISTS `online_admission_nata_details`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `online_admission_nata_details` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `online_admission_id` int(11) NOT NULL,
  `nata_score` varchar(255) DEFAULT NULL,
  `application_number` varchar(255) DEFAULT NULL,
  `nata_year` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `online_admission_payment`
--

DROP TABLE IF EXISTS `online_admission_payment`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `online_admission_payment` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `online_admission_id` int(11) NOT NULL,
  `paid_amount` float(10,2) NOT NULL,
  `payment_mode` varchar(50) NOT NULL,
  `payment_type` varchar(100) NOT NULL,
  `transaction_id` varchar(100) NOT NULL,
  `note` varchar(100) NOT NULL,
  `date` datetime NOT NULL,
  `processing_charge_type` varchar(255) DEFAULT NULL,
  `processing_charge_value` float(10,2) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `online_admission_id` (`online_admission_id`),
  CONSTRAINT `online_admission_payment_ibfk_1` FOREIGN KEY (`online_admission_id`) REFERENCES `online_admissions` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `online_admission_pg_details`
--

DROP TABLE IF EXISTS `online_admission_pg_details`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `online_admission_pg_details` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `online_admission_id` int(11) NOT NULL,
  `pg_course_id` varchar(255) DEFAULT NULL,
  `qualifying_exam` varchar(255) DEFAULT NULL,
  `branch` varchar(255) DEFAULT NULL,
  `year_of_passing` varchar(255) DEFAULT NULL,
  `college_name` varchar(255) DEFAULT NULL,
  `university_name` varchar(255) DEFAULT NULL,
  `university_id` int(11) DEFAULT NULL COMMENT 'Foreign key to online_admission_universities',
  `tancet_pgeta_app_no` varchar(255) DEFAULT NULL,
  `tancet_pgeta_year` varchar(255) DEFAULT NULL,
  `tancet_pgeta_score` varchar(255) DEFAULT NULL,
  `is_alumni` tinyint(1) DEFAULT 0,
  `bonafide_cert_path` varchar(255) DEFAULT NULL,
  `is_sports_person` tinyint(1) DEFAULT 0,
  `sports_level` varchar(255) DEFAULT NULL,
  `is_ex_service` tinyint(1) DEFAULT 0,
  `is_differently_abled` tinyint(1) DEFAULT 0,
  `disability_type` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `university_id_idx` (`university_id`),
  CONSTRAINT `fk_pg_details_university` FOREIGN KEY (`university_id`) REFERENCES `online_admission_universities` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `online_admission_references`
--

DROP TABLE IF EXISTS `online_admission_references`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `online_admission_references` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `online_admission_id` int(11) NOT NULL,
  `referrer_name` varchar(255) DEFAULT NULL,
  `relationship` varchar(255) DEFAULT NULL,
  `phone_no` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=68 DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `online_admission_ug_details`
--

DROP TABLE IF EXISTS `online_admission_ug_details`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `online_admission_ug_details` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `online_admission_id` int(11) NOT NULL,
  `ug_course_id` varchar(255) DEFAULT NULL,
  `school_name_x` varchar(255) DEFAULT NULL,
  `passing_year_x` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=28 DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `online_admission_universities`
--

DROP TABLE IF EXISTS `online_admission_universities`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `online_admission_universities` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL COMMENT 'University name',
  `status` tinyint(1) DEFAULT 1 COMMENT '1 = Active, 0 = Inactive',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `name_unique` (`name`),
  KEY `status_idx` (`status`)
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Universities available for PG admission forms';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `online_admissions`
--

DROP TABLE IF EXISTS `online_admissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `online_admissions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `admission_no` varchar(100) DEFAULT NULL,
  `roll_no` varchar(100) DEFAULT NULL,
  `reference_no` varchar(50) NOT NULL,
  `admission_date` date DEFAULT NULL,
  `firstname` varchar(100) DEFAULT NULL,
  `middlename` varchar(255) NOT NULL,
  `lastname` varchar(100) DEFAULT NULL,
  `rte` varchar(20) NOT NULL DEFAULT 'No',
  `image` varchar(255) DEFAULT NULL,
  `mobileno` varchar(100) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `state` varchar(100) DEFAULT NULL,
  `city` varchar(100) DEFAULT NULL,
  `pincode` varchar(100) DEFAULT NULL,
  `religion` varchar(100) DEFAULT NULL,
  `cast` varchar(50) NOT NULL,
  `dob` date DEFAULT NULL,
  `gender` varchar(100) DEFAULT NULL,
  `current_address` text DEFAULT NULL,
  `permanent_address` text DEFAULT NULL,
  `category_id` int(11) DEFAULT NULL,
  `class_section_id` int(11) DEFAULT NULL,
  `route_id` int(11) NOT NULL,
  `school_house_id` int(11) DEFAULT NULL,
  `blood_group` varchar(200) NOT NULL,
  `vehroute_id` int(11) NOT NULL,
  `hostel_room_id` int(11) DEFAULT NULL,
  `adhar_no` varchar(100) DEFAULT NULL,
  `samagra_id` varchar(100) DEFAULT NULL,
  `bank_account_no` varchar(100) DEFAULT NULL,
  `bank_name` varchar(100) DEFAULT NULL,
  `ifsc_code` varchar(100) DEFAULT NULL,
  `guardian_is` varchar(100) NOT NULL,
  `father_name` varchar(100) DEFAULT NULL,
  `father_phone` varchar(100) DEFAULT NULL,
  `father_occupation` varchar(100) DEFAULT NULL,
  `mother_name` varchar(100) DEFAULT NULL,
  `mother_phone` varchar(100) DEFAULT NULL,
  `mother_occupation` varchar(100) DEFAULT NULL,
  `guardian_name` varchar(100) DEFAULT NULL,
  `guardian_relation` varchar(100) DEFAULT NULL,
  `guardian_phone` varchar(100) DEFAULT NULL,
  `guardian_occupation` varchar(150) NOT NULL,
  `guardian_address` text DEFAULT NULL,
  `guardian_email` varchar(100) NOT NULL,
  `father_pic` varchar(255) NOT NULL,
  `mother_pic` varchar(255) NOT NULL,
  `guardian_pic` varchar(255) NOT NULL,
  `is_enroll` int(11) DEFAULT 0,
  `previous_school` text DEFAULT NULL,
  `height` varchar(100) NOT NULL,
  `weight` varchar(100) NOT NULL,
  `note` text NOT NULL,
  `payment_updated_by` int(11) DEFAULT NULL,
  `payment_updated_at` timestamp NULL DEFAULT NULL,
  `form_status` int(11) NOT NULL,
  `applicant_password` varchar(255) DEFAULT NULL,
  `enquiry_id` int(11) DEFAULT NULL,
  `referred_by_employee_id` int(11) DEFAULT NULL,
  `transaction_id` varchar(255) DEFAULT NULL,
  `paid_status` int(11) NOT NULL,
  `measurement_date` date DEFAULT NULL,
  `app_key` text DEFAULT NULL,
  `document` text DEFAULT NULL,
  `submit_date` date DEFAULT NULL,
  `disable_at` date DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `total_maths` decimal(5,2) DEFAULT NULL COMMENT 'Total marks in Maths subject',
  `maths_marks` decimal(5,2) DEFAULT NULL COMMENT 'Marks obtained in Maths (M)',
  `maths_perc` decimal(5,2) DEFAULT NULL COMMENT 'Percentage in Maths',
  `total_physics` decimal(5,2) DEFAULT NULL COMMENT 'Total marks in Physics subject',
  `physics_marks` decimal(5,2) DEFAULT NULL COMMENT 'Marks obtained in Physics (P)',
  `physics_perc` decimal(5,2) DEFAULT NULL COMMENT 'Percentage in Physics',
  `total_chemistry` decimal(5,2) DEFAULT NULL COMMENT 'Total marks in Chemistry subject',
  `chemistry_marks` decimal(5,2) DEFAULT NULL COMMENT 'Marks obtained in Chemistry (C)',
  `chemistry_perc` decimal(5,2) DEFAULT NULL COMMENT 'Percentage in Chemistry',
  `average_marks` decimal(5,2) DEFAULT NULL COMMENT 'Average: (P+C+M)/3',
  `cutoff_marks` decimal(5,2) DEFAULT NULL COMMENT 'Cut Off: (P+C)/2 + M',
  `ug_course_id` int(11) DEFAULT NULL,
  `tenth_marks_percentage` decimal(5,2) DEFAULT NULL COMMENT 'X std marks percentage',
  `school_name_x` varchar(255) DEFAULT NULL COMMENT 'Name of X std school',
  `passing_year_x` varchar(20) DEFAULT NULL,
  `admission_course_id` int(11) DEFAULT NULL,
  `course_level` varchar(10) DEFAULT NULL,
  `admission_type` varchar(20) DEFAULT NULL,
  `quota_type` varchar(20) DEFAULT NULL,
  `course_fee_total` decimal(12,2) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `class_section_id` (`class_section_id`),
  KEY `category_id` (`category_id`),
  KEY `hostel_room_id` (`hostel_room_id`),
  KEY `school_house_id` (`school_house_id`),
  KEY `idx_reference_no` (`reference_no`),
  KEY `idx_mobileno` (`mobileno`),
  CONSTRAINT `online_admissions_ibfk_1` FOREIGN KEY (`class_section_id`) REFERENCES `class_sections` (`id`) ON DELETE CASCADE,
  CONSTRAINT `online_admissions_ibfk_2` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE CASCADE,
  CONSTRAINT `online_admissions_ibfk_3` FOREIGN KEY (`hostel_room_id`) REFERENCES `hostel_rooms` (`id`) ON DELETE CASCADE,
  CONSTRAINT `online_admissions_ibfk_4` FOREIGN KEY (`school_house_id`) REFERENCES `school_houses` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=217 DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `onlineexam`
--

DROP TABLE IF EXISTS `onlineexam`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `onlineexam` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `session_id` int(11) DEFAULT NULL,
  `exam` text DEFAULT NULL,
  `attempt` int(11) NOT NULL,
  `exam_from` datetime DEFAULT NULL,
  `exam_to` datetime DEFAULT NULL,
  `is_quiz` int(11) NOT NULL DEFAULT 0,
  `auto_publish_date` datetime DEFAULT NULL,
  `time_from` time DEFAULT NULL,
  `time_to` time DEFAULT NULL,
  `duration` time NOT NULL,
  `passing_percentage` float NOT NULL DEFAULT 0,
  `description` text DEFAULT NULL,
  `publish_result` int(11) NOT NULL DEFAULT 0,
  `answer_word_count` int(11) NOT NULL DEFAULT -1,
  `is_active` varchar(1) DEFAULT '0',
  `is_marks_display` int(11) NOT NULL DEFAULT 0,
  `is_neg_marking` int(11) NOT NULL DEFAULT 0,
  `is_random_question` int(11) NOT NULL DEFAULT 0,
  `is_rank_generated` int(11) NOT NULL DEFAULT 0,
  `publish_exam_notification` int(11) NOT NULL,
  `publish_result_notification` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `session_id` (`session_id`),
  CONSTRAINT `onlineexam_ibfk_1` FOREIGN KEY (`session_id`) REFERENCES `sessions` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `onlineexam_attempts`
--

DROP TABLE IF EXISTS `onlineexam_attempts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `onlineexam_attempts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `onlineexam_student_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `onlineexam_student_id` (`onlineexam_student_id`),
  CONSTRAINT `onlineexam_attempts_ibfk_1` FOREIGN KEY (`onlineexam_student_id`) REFERENCES `onlineexam_students` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `onlineexam_questions`
--

DROP TABLE IF EXISTS `onlineexam_questions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `onlineexam_questions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `question_id` int(11) DEFAULT NULL,
  `onlineexam_id` int(11) DEFAULT NULL,
  `session_id` int(11) DEFAULT NULL,
  `marks` float(10,2) NOT NULL DEFAULT 0.00,
  `neg_marks` float(10,2) DEFAULT 0.00,
  `is_active` varchar(1) DEFAULT '0',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `onlineexam_id` (`onlineexam_id`),
  KEY `question_id` (`question_id`),
  KEY `session_id` (`session_id`),
  CONSTRAINT `onlineexam_questions_ibfk_1` FOREIGN KEY (`onlineexam_id`) REFERENCES `onlineexam` (`id`) ON DELETE CASCADE,
  CONSTRAINT `onlineexam_questions_ibfk_2` FOREIGN KEY (`question_id`) REFERENCES `questions` (`id`) ON DELETE CASCADE,
  CONSTRAINT `onlineexam_questions_ibfk_3` FOREIGN KEY (`session_id`) REFERENCES `sessions` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `onlineexam_student_results`
--

DROP TABLE IF EXISTS `onlineexam_student_results`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `onlineexam_student_results` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `onlineexam_student_id` int(11) NOT NULL,
  `onlineexam_question_id` int(11) NOT NULL,
  `select_option` longtext DEFAULT NULL,
  `marks` float(10,2) NOT NULL DEFAULT 0.00,
  `remark` text DEFAULT NULL,
  `attachment_name` text DEFAULT NULL,
  `attachment_upload_name` varchar(250) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `onlineexam_student_id` (`onlineexam_student_id`),
  KEY `onlineexam_question_id` (`onlineexam_question_id`),
  CONSTRAINT `onlineexam_student_results_ibfk_1` FOREIGN KEY (`onlineexam_student_id`) REFERENCES `onlineexam_students` (`id`) ON DELETE CASCADE,
  CONSTRAINT `onlineexam_student_results_ibfk_2` FOREIGN KEY (`onlineexam_question_id`) REFERENCES `onlineexam_questions` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `onlineexam_students`
--

DROP TABLE IF EXISTS `onlineexam_students`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `onlineexam_students` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `onlineexam_id` int(11) DEFAULT NULL,
  `student_session_id` int(11) DEFAULT NULL,
  `is_attempted` int(11) NOT NULL DEFAULT 0,
  `rank` int(11) DEFAULT 0,
  `quiz_attempted` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `candidate_type` enum('student','applicant') NOT NULL DEFAULT 'student',
  `online_admission_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `onlineexam_id` (`onlineexam_id`),
  KEY `student_session_id` (`student_session_id`),
  KEY `idx_oes_online_admission_id` (`online_admission_id`),
  CONSTRAINT `onlineexam_students_ibfk_1` FOREIGN KEY (`onlineexam_id`) REFERENCES `onlineexam` (`id`) ON DELETE CASCADE,
  CONSTRAINT `onlineexam_students_ibfk_2` FOREIGN KEY (`student_session_id`) REFERENCES `student_session` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `payment_settings`
--

DROP TABLE IF EXISTS `payment_settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `payment_settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `payment_type` varchar(200) NOT NULL,
  `api_username` varchar(200) DEFAULT NULL,
  `api_secret_key` varchar(200) NOT NULL,
  `salt` varchar(200) NOT NULL,
  `api_publishable_key` varchar(200) NOT NULL,
  `api_password` varchar(200) DEFAULT NULL,
  `api_signature` varchar(200) DEFAULT NULL,
  `api_email` varchar(200) DEFAULT NULL,
  `paypal_demo` varchar(100) NOT NULL,
  `account_no` varchar(200) NOT NULL,
  `is_active` varchar(255) DEFAULT 'no',
  `gateway_mode` int(11) NOT NULL COMMENT '0 Testing, 1 live',
  `paytm_website` varchar(255) NOT NULL,
  `paytm_industrytype` varchar(255) NOT NULL,
  `charge_type` varchar(255) DEFAULT NULL,
  `charge_value` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `payroll_allowance_types`
--

DROP TABLE IF EXISTS `payroll_allowance_types`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `payroll_allowance_types` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `allowance_code` varchar(20) NOT NULL COMMENT 'Short code: DA, HRA, SA',
  `allowance_name` varchar(100) NOT NULL COMMENT 'Full name: Dearness Allowance',
  `category` enum('earning','deduction') NOT NULL COMMENT 'Earning or Deduction',
  `is_taxable` tinyint(1) DEFAULT 1 COMMENT 'Include in taxable income (1=Yes, 0=No)',
  `is_statutory` tinyint(1) DEFAULT 0 COMMENT 'Auto-calculated by system (EPF, ESI, TDS)',
  `is_active` tinyint(1) DEFAULT 1 COMMENT 'Enable/Disable this type',
  `display_order` int(11) DEFAULT 0 COMMENT 'Sort order in dropdowns',
  `description` text DEFAULT NULL COMMENT 'Admin reference notes',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `allowance_code` (`allowance_code`),
  KEY `idx_category` (`category`),
  KEY `idx_active` (`is_active`)
) ENGINE=InnoDB AUTO_INCREMENT=72 DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci COMMENT='Master list of standardized allowance/deduction types';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `payroll_settings`
--

DROP TABLE IF EXISTS `payroll_settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `payroll_settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `tax_regime` varchar(10) DEFAULT 'new' COMMENT 'Current tax regime: new or old',
  `epf_enabled` tinyint(1) DEFAULT 1 COMMENT 'Whether EPF calculation is enabled',
  `epf_wage_ceiling` decimal(10,2) DEFAULT 15000.00 COMMENT 'EPF wage ceiling',
  `include_da_in_epf` tinyint(1) DEFAULT 1 COMMENT 'Whether DA is included in EPF wage',
  `standard_deduction` decimal(10,2) DEFAULT 75000.00 COMMENT 'Standard deduction for new regime',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Payroll settings for tax and EPF calculations';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `payslip_allowance`
--

DROP TABLE IF EXISTS `payslip_allowance`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `payslip_allowance` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `payslip_id` int(11) NOT NULL,
  `allowance_type` varchar(200) NOT NULL,
  `amount` float NOT NULL,
  `is_temporary` tinyint(1) DEFAULT 0 COMMENT 'TRUE if this is temporary increment display',
  `increment_history_id` int(11) DEFAULT NULL COMMENT 'Links to staff_increment_history',
  `merged_into` varchar(50) DEFAULT NULL COMMENT 'Tracks where increment was merged',
  `staff_id` int(11) NOT NULL,
  `cal_type` varchar(100) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `staff_id` (`staff_id`),
  KEY `payslip_id` (`payslip_id`),
  KEY `FK_payslip_increment` (`increment_history_id`),
  KEY `idx_payslip_allowance_temporary` (`is_temporary`,`increment_history_id`),
  CONSTRAINT `FK_payslip_increment` FOREIGN KEY (`increment_history_id`) REFERENCES `staff_increment_history` (`id`) ON DELETE SET NULL,
  CONSTRAINT `payslip_allowance_ibfk_1` FOREIGN KEY (`staff_id`) REFERENCES `staff` (`id`) ON DELETE CASCADE,
  CONSTRAINT `payslip_allowance_ibfk_2` FOREIGN KEY (`payslip_id`) REFERENCES `staff_payslip` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=109712 DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `permission_category`
--

DROP TABLE IF EXISTS `permission_category`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `permission_category` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `perm_group_id` int(11) DEFAULT NULL,
  `name` varchar(100) DEFAULT NULL,
  `short_code` varchar(100) DEFAULT NULL,
  `enable_view` int(11) DEFAULT 0,
  `enable_add` int(11) DEFAULT 0,
  `enable_edit` int(11) DEFAULT 0,
  `enable_delete` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_short_code` (`short_code`),
  KEY `perm_group_id` (`perm_group_id`),
  CONSTRAINT `permission_category_ibfk_1` FOREIGN KEY (`perm_group_id`) REFERENCES `permission_group` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=15013 DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `permission_group`
--

DROP TABLE IF EXISTS `permission_group`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `permission_group` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) DEFAULT NULL,
  `short_code` varchar(100) NOT NULL,
  `is_active` int(11) DEFAULT 0,
  `system` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1501 DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `permission_student`
--

DROP TABLE IF EXISTS `permission_student`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `permission_student` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) DEFAULT NULL,
  `short_code` varchar(100) NOT NULL,
  `system` int(11) NOT NULL,
  `student` int(11) NOT NULL,
  `parent` int(11) NOT NULL,
  `group_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `group_id` (`group_id`),
  CONSTRAINT `permission_student_ibfk_1` FOREIGN KEY (`group_id`) REFERENCES `permission_group` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=901 DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `pickup_point`
--

DROP TABLE IF EXISTS `pickup_point`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pickup_point` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `latitude` varchar(100) DEFAULT NULL,
  `longitude` varchar(100) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=347 DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `print_headerfooter`
--

DROP TABLE IF EXISTS `print_headerfooter`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `print_headerfooter` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `print_type` varchar(255) NOT NULL,
  `header_image` varchar(255) NOT NULL,
  `footer_content` text NOT NULL,
  `created_by` int(11) NOT NULL,
  `entry_date` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `questions`
--

DROP TABLE IF EXISTS `questions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `questions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `staff_id` int(11) DEFAULT NULL,
  `subject_id` int(11) DEFAULT NULL,
  `question_type` varchar(100) NOT NULL,
  `level` varchar(10) NOT NULL,
  `class_id` int(11) NOT NULL,
  `section_id` int(11) DEFAULT NULL,
  `class_section_id` int(11) DEFAULT NULL,
  `question` text DEFAULT NULL,
  `opt_a` text DEFAULT NULL,
  `opt_b` text DEFAULT NULL,
  `opt_c` text DEFAULT NULL,
  `opt_d` text DEFAULT NULL,
  `opt_e` text DEFAULT NULL,
  `correct` text DEFAULT NULL,
  `descriptive_word_limit` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `subject_id` (`subject_id`),
  KEY `staff_id` (`staff_id`),
  KEY `class_id` (`class_id`),
  KEY `section_id` (`section_id`),
  KEY `class_section_id` (`class_section_id`),
  CONSTRAINT `questions_ibfk_1` FOREIGN KEY (`subject_id`) REFERENCES `subjects` (`id`) ON DELETE CASCADE,
  CONSTRAINT `questions_ibfk_2` FOREIGN KEY (`staff_id`) REFERENCES `staff` (`id`) ON DELETE CASCADE,
  CONSTRAINT `questions_ibfk_3` FOREIGN KEY (`subject_id`) REFERENCES `subjects` (`id`) ON DELETE CASCADE,
  CONSTRAINT `questions_ibfk_4` FOREIGN KEY (`class_id`) REFERENCES `classes` (`id`) ON DELETE CASCADE,
  CONSTRAINT `questions_ibfk_5` FOREIGN KEY (`section_id`) REFERENCES `sections` (`id`) ON DELETE CASCADE,
  CONSTRAINT `questions_ibfk_6` FOREIGN KEY (`class_section_id`) REFERENCES `class_sections` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `read_notification`
--

DROP TABLE IF EXISTS `read_notification`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `read_notification` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `student_id` int(11) DEFAULT NULL,
  `parent_id` int(11) DEFAULT NULL,
  `staff_id` int(11) DEFAULT NULL,
  `notification_id` int(11) DEFAULT NULL,
  `is_active` varchar(255) DEFAULT 'no',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `notification_id` (`notification_id`),
  KEY `staff_id` (`staff_id`),
  KEY `student_id` (`student_id`),
  CONSTRAINT `read_notification_ibfk_1` FOREIGN KEY (`notification_id`) REFERENCES `send_notification` (`id`) ON DELETE CASCADE,
  CONSTRAINT `read_notification_ibfk_2` FOREIGN KEY (`staff_id`) REFERENCES `staff` (`id`) ON DELETE CASCADE,
  CONSTRAINT `read_notification_ibfk_3` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `reference`
--

DROP TABLE IF EXISTS `reference`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `reference` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `reference` varchar(100) NOT NULL,
  `description` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `resume_additional_fields_settings`
--

DROP TABLE IF EXISTS `resume_additional_fields_settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `resume_additional_fields_settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(250) DEFAULT NULL,
  `status` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `resume_settings_fields`
--

DROP TABLE IF EXISTS `resume_settings_fields`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `resume_settings_fields` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(250) DEFAULT NULL,
  `status` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=36 DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `roles`
--

DROP TABLE IF EXISTS `roles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `roles` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) DEFAULT NULL,
  `slug` varchar(150) DEFAULT NULL,
  `is_active` int(11) DEFAULT 0,
  `is_system` int(11) NOT NULL DEFAULT 0,
  `is_superadmin` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `roles_permissions`
--

DROP TABLE IF EXISTS `roles_permissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `roles_permissions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `role_id` int(11) DEFAULT NULL,
  `perm_cat_id` int(11) DEFAULT NULL,
  `can_view` int(11) DEFAULT NULL,
  `can_add` int(11) DEFAULT NULL,
  `can_edit` int(11) DEFAULT NULL,
  `can_delete` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `role_id` (`role_id`),
  KEY `perm_cat_id` (`perm_cat_id`),
  CONSTRAINT `roles_permissions_ibfk_1` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE,
  CONSTRAINT `roles_permissions_ibfk_2` FOREIGN KEY (`perm_cat_id`) REFERENCES `permission_category` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2477 DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `room_types`
--

DROP TABLE IF EXISTS `room_types`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `room_types` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `room_type` varchar(200) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `route_pickup_point`
--

DROP TABLE IF EXISTS `route_pickup_point`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `route_pickup_point` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `session_id` int(11) DEFAULT NULL,
  `transport_route_id` int(11) NOT NULL,
  `pickup_point_id` int(11) NOT NULL,
  `fees` float(10,2) DEFAULT 0.00,
  `destination_distance` float(10,1) DEFAULT 0.0,
  `pickup_time` time DEFAULT NULL,
  `order_number` float NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `transport_route_id` (`transport_route_id`),
  KEY `pickup_point_id` (`pickup_point_id`),
  CONSTRAINT `route_pickup_point_ibfk_1` FOREIGN KEY (`transport_route_id`) REFERENCES `transport_route` (`id`) ON DELETE CASCADE,
  CONSTRAINT `route_pickup_point_ibfk_2` FOREIGN KEY (`pickup_point_id`) REFERENCES `pickup_point` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=138 DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `sch_settings`
--

DROP TABLE IF EXISTS `sch_settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sch_settings` (
  `id` int(11) NOT NULL,
  `institution_type` enum('school','college') NOT NULL DEFAULT 'school',
  `base_url` varchar(500) DEFAULT NULL,
  `folder_path` text DEFAULT NULL,
  `name` varchar(100) DEFAULT NULL,
  `student_biometric` int(11) NOT NULL DEFAULT 0,
  `staff_biometric` int(11) NOT NULL DEFAULT 0,
  `biometric` tinyint(1) DEFAULT 0,
  `biometric_device` text DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `phone` varchar(50) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `lang_id` int(11) DEFAULT NULL,
  `languages` varchar(500) NOT NULL,
  `dise_code` varchar(50) DEFAULT NULL,
  `date_format` varchar(50) NOT NULL,
  `time_format` varchar(255) NOT NULL,
  `currency` varchar(50) NOT NULL,
  `currency_symbol` varchar(50) NOT NULL,
  `is_rtl` varchar(10) DEFAULT 'disabled',
  `is_duplicate_fees_invoice` varchar(100) DEFAULT '0',
  `collect_back_date_fees` int(11) NOT NULL,
  `single_page_print` int(11) DEFAULT 0,
  `timezone` varchar(30) DEFAULT 'UTC',
  `session_id` int(11) DEFAULT NULL,
  `cron_secret_key` varchar(100) NOT NULL,
  `currency_place` varchar(50) NOT NULL DEFAULT 'before_number',
  `currency_format` varchar(20) DEFAULT NULL,
  `class_teacher` varchar(100) NOT NULL,
  `start_month` varchar(40) NOT NULL,
  `attendence_type` int(11) NOT NULL DEFAULT 0,
  `low_attendance_limit` float(10,2) NOT NULL,
  `image` varchar(100) DEFAULT NULL,
  `admin_logo` varchar(255) NOT NULL,
  `admin_small_logo` varchar(255) NOT NULL,
  `admin_login_page_background` varchar(255) NOT NULL,
  `user_login_page_background` varchar(255) NOT NULL,
  `theme` varchar(200) NOT NULL DEFAULT 'default.jpg',
  `fee_due_days` int(11) DEFAULT 0,
  `adm_auto_insert` int(11) NOT NULL DEFAULT 1,
  `adm_include_current_year` tinyint(1) NOT NULL DEFAULT 0,
  `adm_prefix` varchar(50) NOT NULL DEFAULT 'ssadm19/20',
  `adm_start_from` varchar(11) NOT NULL,
  `adm_no_digit` int(11) NOT NULL DEFAULT 6,
  `adm_update_status` int(11) NOT NULL DEFAULT 0,
  `staffid_auto_insert` int(11) NOT NULL DEFAULT 1,
  `staffid_include_current_year` tinyint(1) NOT NULL DEFAULT 0,
  `period_start_time` varchar(10) DEFAULT NULL,
  `duration_minute` int(11) DEFAULT NULL,
  `interval_minute` int(11) DEFAULT NULL,
  `staffid_prefix` varchar(100) NOT NULL DEFAULT 'staffss/19/20',
  `staffid_start_from` varchar(50) NOT NULL,
  `staffid_no_digit` int(11) NOT NULL DEFAULT 6,
  `staffid_update_status` int(11) NOT NULL DEFAULT 0,
  `is_active` varchar(255) DEFAULT 'no',
  `online_admission` int(11) DEFAULT 0,
  `online_admission_payment` varchar(50) NOT NULL,
  `online_admission_amount` float NOT NULL,
  `online_admission_instruction` text NOT NULL,
  `online_admission_conditions` text NOT NULL,
  `online_admission_processing_charge_type` varchar(20) DEFAULT 'fixed',
  `online_admission_processing_charge` decimal(10,2) DEFAULT 0.00,
  `onlineform_sub_merchant_id` varchar(255) DEFAULT NULL,
  `online_admission_application_form` varchar(255) DEFAULT NULL,
  `exam_result` int(11) NOT NULL,
  `is_blood_group` int(11) NOT NULL DEFAULT 1,
  `is_student_house` int(11) NOT NULL DEFAULT 1,
  `roll_no` int(11) NOT NULL DEFAULT 1,
  `category` int(11) NOT NULL,
  `religion` int(11) NOT NULL DEFAULT 1,
  `cast` int(11) NOT NULL DEFAULT 1,
  `mobile_no` int(11) NOT NULL DEFAULT 1,
  `student_email` int(11) NOT NULL DEFAULT 1,
  `admission_date` int(11) NOT NULL DEFAULT 1,
  `lastname` int(11) NOT NULL,
  `middlename` int(11) NOT NULL DEFAULT 1,
  `student_photo` int(11) NOT NULL DEFAULT 1,
  `student_height` int(11) NOT NULL DEFAULT 1,
  `student_weight` int(11) NOT NULL DEFAULT 1,
  `measurement_date` int(11) NOT NULL DEFAULT 1,
  `father_name` int(11) NOT NULL DEFAULT 1,
  `father_phone` int(11) NOT NULL DEFAULT 1,
  `father_occupation` int(11) NOT NULL DEFAULT 1,
  `father_pic` int(11) NOT NULL DEFAULT 1,
  `mother_name` int(11) NOT NULL DEFAULT 1,
  `mother_phone` int(11) NOT NULL DEFAULT 1,
  `mother_occupation` int(11) NOT NULL DEFAULT 1,
  `mother_pic` int(11) NOT NULL DEFAULT 1,
  `guardian_name` int(11) NOT NULL,
  `guardian_relation` int(11) NOT NULL DEFAULT 1,
  `guardian_phone` int(11) NOT NULL,
  `guardian_email` int(11) NOT NULL DEFAULT 1,
  `guardian_pic` int(11) NOT NULL DEFAULT 1,
  `guardian_occupation` int(11) NOT NULL,
  `guardian_address` int(11) NOT NULL DEFAULT 1,
  `current_address` int(11) NOT NULL DEFAULT 1,
  `permanent_address` int(11) NOT NULL DEFAULT 1,
  `route_list` int(11) NOT NULL DEFAULT 1,
  `hostel_id` int(11) NOT NULL DEFAULT 1,
  `bank_account_no` int(11) NOT NULL DEFAULT 1,
  `ifsc_code` int(11) NOT NULL,
  `bank_name` int(11) NOT NULL,
  `national_identification_no` int(11) NOT NULL DEFAULT 1,
  `local_identification_no` int(11) NOT NULL DEFAULT 1,
  `rte` int(11) NOT NULL DEFAULT 1,
  `previous_school_details` int(11) NOT NULL DEFAULT 1,
  `student_note` int(11) NOT NULL DEFAULT 1,
  `upload_documents` int(11) NOT NULL DEFAULT 1,
  `student_barcode` int(11) NOT NULL DEFAULT 1,
  `staff_designation` int(11) NOT NULL DEFAULT 1,
  `staff_department` int(11) NOT NULL DEFAULT 1,
  `staff_last_name` int(11) NOT NULL DEFAULT 1,
  `staff_father_name` int(11) NOT NULL DEFAULT 1,
  `staff_mother_name` int(11) NOT NULL DEFAULT 1,
  `staff_date_of_joining` int(11) NOT NULL DEFAULT 1,
  `staff_phone` int(11) NOT NULL DEFAULT 1,
  `staff_emergency_contact` int(11) NOT NULL DEFAULT 1,
  `staff_marital_status` int(11) NOT NULL DEFAULT 1,
  `staff_photo` int(11) NOT NULL DEFAULT 1,
  `staff_current_address` int(11) NOT NULL DEFAULT 1,
  `staff_permanent_address` int(11) NOT NULL DEFAULT 1,
  `staff_qualification` int(11) NOT NULL DEFAULT 1,
  `staff_work_experience` int(11) NOT NULL DEFAULT 1,
  `staff_note` int(11) NOT NULL DEFAULT 1,
  `staff_epf_no` int(11) NOT NULL DEFAULT 1,
  `staff_basic_salary` int(11) NOT NULL DEFAULT 1,
  `staff_contract_type` int(11) NOT NULL DEFAULT 1,
  `staff_work_shift` int(11) NOT NULL DEFAULT 1,
  `staff_work_location` int(11) NOT NULL DEFAULT 1,
  `staff_leaves` int(11) NOT NULL DEFAULT 1,
  `staff_account_details` int(11) NOT NULL DEFAULT 1,
  `staff_social_media` int(11) NOT NULL DEFAULT 1,
  `staff_upload_documents` int(11) NOT NULL DEFAULT 1,
  `staff_barcode` int(11) NOT NULL DEFAULT 1,
  `staff_notification_email` varchar(50) NOT NULL,
  `mobile_api_url` tinytext NOT NULL,
  `app_primary_color_code` varchar(20) DEFAULT NULL,
  `app_secondary_color_code` varchar(20) DEFAULT NULL,
  `app_logo` varchar(250) DEFAULT NULL,
  `zoom_api_key` varchar(100) DEFAULT NULL,
  `zoom_api_secret` varchar(100) DEFAULT NULL,
  `student_profile_edit` int(11) NOT NULL DEFAULT 0,
  `staff_profile_edit` tinyint(1) NOT NULL DEFAULT 0,
  `start_week` varchar(10) NOT NULL,
  `my_question` int(11) NOT NULL,
  `superadmin_restriction` varchar(20) NOT NULL,
  `student_timeline` varchar(20) NOT NULL,
  `calendar_event_reminder` int(11) DEFAULT NULL,
  `event_reminder` varchar(20) NOT NULL,
  `auto_adjust_lop_with_leaves` tinyint(1) NOT NULL DEFAULT 0 COMMENT '0=Require approved leave applications, 1=Auto-adjust from available paid leaves',
  `auto_adjust_lop_with_preallotted_leaves` tinyint(1) NOT NULL DEFAULT 0,
  `student_login` varchar(100) DEFAULT NULL,
  `parent_login` varchar(100) DEFAULT NULL,
  `student_panel_login` int(11) NOT NULL DEFAULT 1,
  `parent_panel_login` int(11) NOT NULL DEFAULT 1,
  `is_student_feature_lock` int(11) NOT NULL DEFAULT 0,
  `maintenance_mode` int(11) NOT NULL DEFAULT 0,
  `lock_grace_period` int(11) NOT NULL DEFAULT 0,
  `is_offline_fee_payment` int(11) NOT NULL DEFAULT 0,
  `offline_bank_payment_instruction` text NOT NULL,
  `scan_code_type` varchar(50) NOT NULL DEFAULT 'barcode',
  `student_resume_download` int(11) NOT NULL DEFAULT 1,
  `download_admit_card` int(11) NOT NULL DEFAULT 0,
  `fees_discount` int(11) NOT NULL,
  `front_side_whatsapp` int(11) NOT NULL DEFAULT 0,
  `front_side_whatsapp_mobile` varchar(50) DEFAULT NULL,
  `front_side_whatsapp_from` time DEFAULT NULL,
  `front_side_whatsapp_to` time DEFAULT NULL,
  `admin_panel_whatsapp` int(11) NOT NULL DEFAULT 0,
  `admin_panel_whatsapp_mobile` varchar(50) DEFAULT NULL,
  `admin_panel_whatsapp_from` time DEFAULT NULL,
  `admin_panel_whatsapp_to` time DEFAULT NULL,
  `student_panel_whatsapp` int(11) NOT NULL DEFAULT 0,
  `student_panel_whatsapp_mobile` varchar(50) DEFAULT NULL,
  `student_panel_whatsapp_from` time DEFAULT NULL,
  `student_panel_whatsapp_to` time DEFAULT NULL,
  `saas_key` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `last_biometric_sync_datetime` datetime DEFAULT NULL,
  `office_end_time` time DEFAULT NULL,
  `morning_session_end_time` time DEFAULT NULL,
  `evening_session_end_time` time DEFAULT NULL,
  `max_late_allowed` int(11) NOT NULL DEFAULT 0,
  `max_permission_allowed` int(11) NOT NULL DEFAULT 0,
  `transport_fee_type` varchar(20) NOT NULL DEFAULT 'monthly',
  `last_processed_attendance_date` date DEFAULT NULL,
  `last_processed_attendance_datetime` datetime DEFAULT NULL,
  `staff_self_edit` tinyint(1) NOT NULL DEFAULT 0,
  `leave_approver_id` int(11) DEFAULT NULL,
  `weekend_days` varchar(50) DEFAULT '0',
  `isSecondSaturdayHoliday` int(11) DEFAULT 0,
  `admission_logo_left` varchar(255) DEFAULT NULL,
  `admission_logo_right` varchar(255) DEFAULT NULL,
  `website` varchar(255) DEFAULT NULL,
  `payroll_cutoff_day` int(11) NOT NULL DEFAULT 0,
  `payroll_fy_start_month` tinyint(4) NOT NULL DEFAULT 4,
  `payroll_fy_end_month` tinyint(4) NOT NULL DEFAULT 3,
  `monthly_leave_increment_enabled` tinyint(1) NOT NULL DEFAULT 0 COMMENT 'Enable monthly leave increment automation',
  `monthly_increment_leave_type_id` int(11) DEFAULT NULL COMMENT 'Leave type ID to increment monthly (for single-type mode)',
  `monthly_increment_days` decimal(5,2) NOT NULL DEFAULT 1.00 COMMENT 'Days to increment per month (for single-type mode)',
  `leave_reset_month` int(11) DEFAULT NULL,
  `leave_substitution_required_roles` text DEFAULT NULL,
  `leave_substitution_exempt_types` text DEFAULT NULL,
  `leave_self_approve_roles` text DEFAULT NULL,
  `leave_workday_override_types` varchar(255) DEFAULT NULL,
  `leave_past_date_allowed_roles` text DEFAULT NULL,
  `leave_enable_half_day` tinyint(1) NOT NULL DEFAULT 1,
  `leave_half_day_allowed_roles` text DEFAULT NULL,
  `leave_half_day_allowed_types` text DEFAULT NULL,
  `last_leave_increment_processed` date DEFAULT NULL COMMENT 'Last date when monthly leave increment was processed',
  `po_fallback_use_department_head_l1` tinyint(1) NOT NULL DEFAULT 1,
  `po_fallback_l2_staff_id` int(11) DEFAULT NULL,
  `po_fallback_superadmin_can_override_l1` tinyint(1) NOT NULL DEFAULT 1,
  `indent_fallback_use_department_head_l1` tinyint(1) NOT NULL DEFAULT 1,
  `indent_fallback_l2_staff_id` int(11) DEFAULT NULL,
  `indent_fallback_superadmin_can_override_l1` tinyint(1) NOT NULL DEFAULT 1,
  `student_max_books_allowed` int(11) NOT NULL DEFAULT 3,
  `staff_max_books_allowed` int(11) NOT NULL DEFAULT 5,
  `student_book_return_days` int(11) NOT NULL DEFAULT 15,
  `staff_book_return_days` int(11) NOT NULL DEFAULT 30,
  `meta_leads_enabled` tinyint(1) NOT NULL DEFAULT 0,
  `meta_verify_token` varchar(255) DEFAULT NULL,
  `meta_page_access_token` text DEFAULT NULL,
  `meta_form_id` varchar(100) DEFAULT NULL,
  `meta_page_id` varchar(64) DEFAULT NULL,
  `meta_default_course_id` int(11) DEFAULT NULL,
  `meta_app_secret` varchar(255) DEFAULT NULL,
  `applicant_class_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `lang_id` (`lang_id`),
  KEY `session_id` (`session_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `school_houses`
--

DROP TABLE IF EXISTS `school_houses`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `school_houses` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `house_name` varchar(200) NOT NULL,
  `description` varchar(400) NOT NULL,
  `is_active` varchar(50) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `sections`
--

DROP TABLE IF EXISTS `sections`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sections` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `section` varchar(60) DEFAULT NULL,
  `is_active` varchar(255) DEFAULT 'no',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `send_notification`
--

DROP TABLE IF EXISTS `send_notification`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `send_notification` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(50) DEFAULT NULL,
  `publish_date` date DEFAULT NULL,
  `date` date DEFAULT NULL,
  `attachment` varchar(500) DEFAULT NULL,
  `message` text DEFAULT NULL,
  `visible_student` varchar(10) NOT NULL DEFAULT 'no',
  `visible_staff` varchar(10) NOT NULL DEFAULT 'no',
  `visible_parent` varchar(10) NOT NULL DEFAULT 'no',
  `created_by` varchar(60) DEFAULT NULL,
  `created_id` int(11) DEFAULT NULL,
  `is_active` varchar(255) DEFAULT 'no',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `created_id` (`created_id`),
  CONSTRAINT `send_notification_ibfk_1` FOREIGN KEY (`created_id`) REFERENCES `staff` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `sessions`
--

DROP TABLE IF EXISTS `sessions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sessions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `session` varchar(60) DEFAULT NULL,
  `is_active` varchar(255) DEFAULT 'no',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=26 DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `share_content_for`
--

DROP TABLE IF EXISTS `share_content_for`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `share_content_for` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `group_id` varchar(20) DEFAULT NULL,
  `student_id` int(11) DEFAULT NULL,
  `user_parent_id` int(11) DEFAULT NULL,
  `staff_id` int(11) DEFAULT NULL,
  `class_section_id` int(11) DEFAULT NULL,
  `share_content_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `upload_content_id` (`share_content_id`),
  KEY `student_id` (`student_id`),
  KEY `staff_id` (`staff_id`),
  KEY `class_section_id` (`class_section_id`),
  KEY `user_parent_id` (`user_parent_id`),
  CONSTRAINT `share_content_for_ibfk_1` FOREIGN KEY (`share_content_id`) REFERENCES `share_contents` (`id`) ON DELETE CASCADE,
  CONSTRAINT `share_content_for_ibfk_2` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`),
  CONSTRAINT `share_content_for_ibfk_3` FOREIGN KEY (`staff_id`) REFERENCES `staff` (`id`),
  CONSTRAINT `share_content_for_ibfk_4` FOREIGN KEY (`class_section_id`) REFERENCES `class_sections` (`id`),
  CONSTRAINT `share_content_for_ibfk_5` FOREIGN KEY (`user_parent_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `share_contents`
--

DROP TABLE IF EXISTS `share_contents`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `share_contents` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `send_to` varchar(50) DEFAULT NULL,
  `title` text DEFAULT NULL,
  `share_date` date DEFAULT NULL,
  `valid_upto` date DEFAULT NULL,
  `description` text DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `created_by` (`created_by`),
  CONSTRAINT `share_contents_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `staff` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `share_upload_contents`
--

DROP TABLE IF EXISTS `share_upload_contents`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `share_upload_contents` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `upload_content_id` int(11) DEFAULT NULL,
  `share_content_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `upload_content_id` (`upload_content_id`),
  KEY `share_content_id` (`share_content_id`),
  CONSTRAINT `share_upload_contents_ibfk_1` FOREIGN KEY (`upload_content_id`) REFERENCES `upload_contents` (`id`) ON DELETE CASCADE,
  CONSTRAINT `share_upload_contents_ibfk_2` FOREIGN KEY (`share_content_id`) REFERENCES `share_contents` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `sidebar_menus`
--

DROP TABLE IF EXISTS `sidebar_menus`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sidebar_menus` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `product_name` varchar(50) NOT NULL,
  `permission_group_id` int(11) DEFAULT NULL,
  `icon` varchar(100) DEFAULT NULL,
  `menu` varchar(500) DEFAULT NULL,
  `activate_menu` varchar(100) DEFAULT NULL,
  `lang_key` varchar(250) NOT NULL,
  `system_level` int(11) DEFAULT 0,
  `level` int(11) DEFAULT NULL,
  `sidebar_display` int(11) DEFAULT 0,
  `access_permissions` text DEFAULT NULL,
  `is_active` int(11) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `permission_group_id` (`permission_group_id`),
  CONSTRAINT `sidebar_menus_ibfk_1` FOREIGN KEY (`permission_group_id`) REFERENCES `permission_group` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=41 DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `sidebar_sub_menus`
--

DROP TABLE IF EXISTS `sidebar_sub_menus`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sidebar_sub_menus` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sidebar_menu_id` int(11) DEFAULT NULL,
  `menu` varchar(500) DEFAULT NULL,
  `key` varchar(500) DEFAULT NULL,
  `lang_key` varchar(250) DEFAULT NULL,
  `url` text DEFAULT NULL,
  `level` int(11) DEFAULT NULL,
  `access_permissions` varchar(500) DEFAULT NULL,
  `permission_group_id` int(11) DEFAULT NULL,
  `activate_controller` varchar(100) DEFAULT NULL COMMENT 'income',
  `activate_methods` varchar(500) DEFAULT NULL COMMENT 'index,edit',
  `addon_permission` varchar(100) DEFAULT NULL,
  `is_active` int(11) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `sidebar_menu_id` (`sidebar_menu_id`),
  KEY `permission_group_id` (`permission_group_id`),
  CONSTRAINT `sidebar_sub_menus_ibfk_1` FOREIGN KEY (`sidebar_menu_id`) REFERENCES `sidebar_menus` (`id`) ON DELETE CASCADE,
  CONSTRAINT `sidebar_sub_menus_ibfk_2` FOREIGN KEY (`permission_group_id`) REFERENCES `permission_group` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=271 DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `sms_config`
--

DROP TABLE IF EXISTS `sms_config`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sms_config` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `type` varchar(50) NOT NULL,
  `name` varchar(100) NOT NULL,
  `api_id` varchar(100) NOT NULL,
  `authkey` varchar(100) NOT NULL,
  `senderid` varchar(100) NOT NULL,
  `contact` text DEFAULT NULL,
  `username` varchar(150) DEFAULT NULL,
  `url` varchar(150) DEFAULT NULL,
  `password` varchar(150) DEFAULT NULL,
  `is_active` varchar(255) DEFAULT 'disabled',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `sms_template`
--

DROP TABLE IF EXISTS `sms_template`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sms_template` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(100) NOT NULL,
  `message` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `source`
--

DROP TABLE IF EXISTS `source`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `source` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `source` varchar(100) NOT NULL,
  `description` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=20 DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `special_attendance_inputs`
--

DROP TABLE IF EXISTS `special_attendance_inputs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `special_attendance_inputs` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `staff_id` int(11) NOT NULL,
  `month` tinyint(3) unsigned NOT NULL,
  `year` smallint(5) unsigned NOT NULL,
  `lop_days` decimal(5,2) NOT NULL DEFAULT 0.00,
  `reason` varchar(255) DEFAULT NULL,
  `admin_user_id` int(11) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_staff_month_year` (`staff_id`,`month`,`year`),
  KEY `idx_month_year` (`month`,`year`),
  KEY `idx_staff` (`staff_id`)
) ENGINE=InnoDB AUTO_INCREMENT=231 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `staff`
--

DROP TABLE IF EXISTS `staff`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `staff` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `prefix` varchar(10) DEFAULT NULL,
  `ug_qualification` varchar(100) DEFAULT NULL,
  `pg_qualification` varchar(100) DEFAULT NULL,
  `higher_qualification` varchar(100) DEFAULT NULL,
  `qualified_exam` varchar(100) DEFAULT NULL,
  `subject_specialization` varchar(255) DEFAULT NULL,
  `additional_qualification` text DEFAULT NULL,
  `employee_id` varchar(200) NOT NULL,
  `biometric_id` varchar(255) DEFAULT NULL,
  `lang_id` int(11) NOT NULL,
  `currency_id` int(11) DEFAULT 0,
  `department` int(11) DEFAULT NULL,
  `designation` int(11) DEFAULT NULL,
  `qualification` varchar(200) NOT NULL,
  `work_exp` varchar(200) NOT NULL,
  `name` varchar(200) NOT NULL,
  `surname` varchar(200) NOT NULL,
  `father_name` varchar(200) NOT NULL,
  `mother_name` varchar(200) NOT NULL,
  `contact_no` varchar(200) NOT NULL,
  `emergency_contact_number` varchar(20) DEFAULT NULL,
  `emergency_contact_no` varchar(200) NOT NULL,
  `email` varchar(200) NOT NULL,
  `dob` date DEFAULT NULL,
  `marital_status` varchar(100) NOT NULL,
  `date_of_joining` date DEFAULT NULL,
  `date_of_leaving` date DEFAULT NULL,
  `local_address` varchar(300) NOT NULL,
  `permanent_address` varchar(200) NOT NULL,
  `work_experience` varchar(255) DEFAULT NULL,
  `note` varchar(200) NOT NULL,
  `image` varchar(200) NOT NULL,
  `password` varchar(250) NOT NULL,
  `gender` varchar(50) NOT NULL,
  `account_title` varchar(200) NOT NULL,
  `bank_account_no` varchar(200) NOT NULL,
  `bank_name` varchar(200) NOT NULL,
  `ifsc_code` varchar(200) NOT NULL,
  `bank_branch` varchar(100) NOT NULL,
  `payscale` varchar(200) NOT NULL,
  `basic_salary` decimal(10,2) DEFAULT NULL COMMENT 'Contracted/appointed basic salary from employment letter. Used as seed for first payslip.',
  `contract_type` varchar(100) NOT NULL,
  `shift` varchar(100) NOT NULL,
  `location` varchar(100) NOT NULL,
  `facebook` varchar(200) NOT NULL,
  `twitter` varchar(200) NOT NULL,
  `linkedin` varchar(200) NOT NULL,
  `instagram` varchar(200) NOT NULL,
  `resume` varchar(200) NOT NULL,
  `joining_letter` varchar(200) NOT NULL,
  `resignation_letter` varchar(200) NOT NULL,
  `other_document_name` varchar(200) NOT NULL,
  `other_document_file` varchar(200) NOT NULL,
  `user_id` int(11) NOT NULL,
  `is_active` int(11) NOT NULL,
  `verification_code` varchar(100) NOT NULL,
  `zoom_api_key` varchar(100) DEFAULT NULL,
  `zoom_api_secret` varchar(100) DEFAULT NULL,
  `disable_at` date DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `app_key` text DEFAULT NULL,
  `aadhaar_no` varchar(255) DEFAULT NULL,
  `religion` varchar(255) DEFAULT NULL,
  `caste` varchar(255) DEFAULT NULL,
  `blood_group` varchar(255) DEFAULT NULL,
  `country` varchar(255) DEFAULT NULL,
  `state` varchar(255) DEFAULT NULL,
  `pincode` varchar(255) DEFAULT NULL,
  `previous_salary` decimal(10,2) DEFAULT NULL,
  `uan_no` varchar(255) DEFAULT NULL,
  `esi_no` varchar(50) DEFAULT NULL COMMENT 'ESI Number for the staff member',
  `is_epf_enabled` tinyint(1) DEFAULT 1 COMMENT 'Enable EPF deduction for this staff member',
  `is_esi_enabled` tinyint(1) DEFAULT 1 COMMENT 'Enable ESI deduction for this staff member',
  `pan_no` varchar(255) DEFAULT NULL,
  `previous_institution` varchar(255) DEFAULT NULL,
  `subject_expertise` varchar(255) DEFAULT NULL,
  `category_id` int(11) DEFAULT NULL,
  `tds_percentage` decimal(5,2) DEFAULT NULL COMMENT 'Flat TDS % override on gross salary. If set, skips new-regime slab calculation.',
  `opening_ytd_income` decimal(15,2) DEFAULT NULL,
  `opening_ytd_tax_deducted` decimal(15,2) DEFAULT NULL,
  `opening_ytd_fy_start_year` smallint(6) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `employee_id` (`employee_id`),
  UNIQUE KEY `biometric_id` (`biometric_id`),
  KEY `designation` (`designation`),
  KEY `department` (`department`),
  KEY `idx_staff_is_epf_enabled` (`is_epf_enabled`),
  KEY `idx_staff_is_esi_enabled` (`is_esi_enabled`),
  KEY `idx_staff_esi_no` (`esi_no`),
  KEY `category_id` (`category_id`),
  CONSTRAINT `staff_ibfk_1` FOREIGN KEY (`designation`) REFERENCES `staff_designation` (`id`) ON DELETE CASCADE,
  CONSTRAINT `staff_ibfk_2` FOREIGN KEY (`department`) REFERENCES `department` (`id`) ON DELETE CASCADE,
  CONSTRAINT `staff_ibfk_3` FOREIGN KEY (`category_id`) REFERENCES `staff_designation_category` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=635 DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `staff_attendance`
--

DROP TABLE IF EXISTS `staff_attendance`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `staff_attendance` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `date` date NOT NULL,
  `staff_id` int(11) NOT NULL,
  `staff_attendance_type_id` int(11) NOT NULL,
  `biometric_attendence` int(11) DEFAULT 0,
  `qrcode_attendance` int(11) NOT NULL DEFAULT 0,
  `biometric_device_data` text DEFAULT NULL,
  `user_agent` varchar(250) DEFAULT NULL,
  `remark` varchar(200) NOT NULL,
  `is_active` int(11) NOT NULL,
  `in_time` time DEFAULT NULL,
  `out_time` time DEFAULT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `total_hours_worked` decimal(5,2) DEFAULT NULL,
  `session_attendance_data` text DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `FK_staff_attendance_staff` (`staff_id`),
  KEY `FK_staff_attendance_staff_attendance_type` (`staff_attendance_type_id`),
  CONSTRAINT `FK_staff_attendance_staff` FOREIGN KEY (`staff_id`) REFERENCES `staff` (`id`) ON DELETE CASCADE,
  CONSTRAINT `FK_staff_attendance_staff_attendance_type` FOREIGN KEY (`staff_attendance_type_id`) REFERENCES `staff_attendance_type` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=522569 DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `staff_attendance_type`
--

DROP TABLE IF EXISTS `staff_attendance_type`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `staff_attendance_type` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `type` varchar(200) NOT NULL,
  `key_value` varchar(200) NOT NULL,
  `is_active` varchar(50) NOT NULL,
  `for_qr_attendance` int(11) NOT NULL DEFAULT 1,
  `long_lang_name` varchar(250) DEFAULT NULL,
  `long_name_style` varchar(250) DEFAULT NULL,
  `for_schedule` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `staff_attendence_schedules`
--

DROP TABLE IF EXISTS `staff_attendence_schedules`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `staff_attendence_schedules` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `staff_attendence_type_id` int(11) DEFAULT NULL,
  `role_id` int(11) DEFAULT NULL,
  `entry_time_from` time DEFAULT NULL,
  `entry_time_to` time DEFAULT NULL,
  `total_institute_hour` time DEFAULT '00:00:00',
  `is_active` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=445 DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `staff_biometric_punches`
--

DROP TABLE IF EXISTS `staff_biometric_punches`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `staff_biometric_punches` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `staff_id` int(11) NOT NULL,
  `punch_time` datetime NOT NULL,
  `is_exception` tinyint(1) NOT NULL DEFAULT 0 COMMENT '1=Exception, 0=Normal',
  `exception_reason` varchar(255) DEFAULT NULL COMMENT 'Reason for exception',
  `exception_resolved` tinyint(1) NOT NULL DEFAULT 0 COMMENT '1=Resolved, 0=Pending',
  `resolved_by` int(11) DEFAULT NULL COMMENT 'Staff ID who resolved',
  `resolved_at` datetime DEFAULT NULL COMMENT 'When resolved',
  `resolution_action` enum('assign_previous_day','assign_current_day','mark_invalid') DEFAULT NULL COMMENT 'Action taken',
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `staff_id` (`staff_id`),
  KEY `idx_exception` (`is_exception`,`exception_resolved`)
) ENGINE=InnoDB AUTO_INCREMENT=296465 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `staff_biometric_punches_manual`
--

DROP TABLE IF EXISTS `staff_biometric_punches_manual`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `staff_biometric_punches_manual` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `staff_id` int(11) NOT NULL,
  `punch_time` datetime NOT NULL,
  `punch_type` enum('in','out') NOT NULL,
  `source` varchar(50) DEFAULT 'manual_adjustment',
  `admin_user_id` int(11) DEFAULT NULL,
  `reason` varchar(255) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_punch` (`staff_id`,`punch_time`,`punch_type`)
) ENGINE=InnoDB AUTO_INCREMENT=244187 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `staff_day_status`
--

DROP TABLE IF EXISTS `staff_day_status`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `staff_day_status` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `staff_id` int(11) NOT NULL,
  `date` date NOT NULL,
  `status` varchar(10) NOT NULL COMMENT 'OD, CPL, FH-OD, SH-OD, FH-CPL, SH-CPL, PL, CL, ML, HOLIDAY, OVERRIDE',
  `source` enum('LEAVE','HOLIDAY','HR_OVERRIDE') NOT NULL DEFAULT 'LEAVE',
  `leave_id` int(11) DEFAULT NULL COMMENT 'FK to leave_requests.id',
  `payroll_impact` enum('PAID_PRESENT','PAID_ABSENT','LOP','HOLIDAY') NOT NULL,
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_staff_date` (`staff_id`,`date`),
  KEY `idx_leave_id` (`leave_id`),
  KEY `idx_staff_date_range` (`staff_id`,`date`)
) ENGINE=InnoDB AUTO_INCREMENT=60 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Locks specific dates for staff to a payroll impact, overriding biometric data for LOP calculation.';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `staff_designation`
--

DROP TABLE IF EXISTS `staff_designation`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `staff_designation` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `designation` varchar(200) NOT NULL,
  `category_id` int(11) DEFAULT NULL,
  `is_active` varchar(100) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `category_id` (`category_id`),
  CONSTRAINT `staff_designation_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `staff_designation_category` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=56 DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `staff_designation_category`
--

DROP TABLE IF EXISTS `staff_designation_category`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `staff_designation_category` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  `description` text DEFAULT NULL,
  `color` varchar(10) DEFAULT NULL,
  `icon` varchar(50) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `staff_id_card`
--

DROP TABLE IF EXISTS `staff_id_card`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `staff_id_card` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `school_name` varchar(255) NOT NULL,
  `school_address` varchar(255) NOT NULL,
  `background` varchar(100) NOT NULL,
  `logo` varchar(100) NOT NULL,
  `sign_image` varchar(100) NOT NULL,
  `header_color` varchar(100) NOT NULL,
  `enable_vertical_card` int(11) NOT NULL DEFAULT 0,
  `enable_staff_role` tinyint(1) NOT NULL COMMENT '0=disable,1=enable',
  `enable_staff_id` tinyint(1) NOT NULL COMMENT '0=disable,1=enable',
  `enable_staff_department` tinyint(1) NOT NULL COMMENT '0=disable,1=enable',
  `enable_designation` tinyint(1) NOT NULL COMMENT '0=disable,1=enable',
  `enable_name` tinyint(1) NOT NULL COMMENT '0=disable,1=enable',
  `enable_fathers_name` tinyint(1) NOT NULL COMMENT '0=disable,1=enable',
  `enable_mothers_name` tinyint(1) NOT NULL COMMENT '0=disable,1=enable',
  `enable_date_of_joining` tinyint(1) NOT NULL COMMENT '0=disable,1=enable',
  `enable_permanent_address` tinyint(1) NOT NULL COMMENT '0=disable,1=enable',
  `enable_staff_dob` tinyint(1) NOT NULL COMMENT '0=disable,1=enable',
  `enable_staff_phone` tinyint(1) NOT NULL COMMENT '0=disable,1=enable',
  `enable_staff_barcode` tinyint(1) NOT NULL COMMENT '0=disable,1=enable',
  `status` tinyint(1) NOT NULL COMMENT '0=disable,1=enable',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `staff_increment_history`
--

DROP TABLE IF EXISTS `staff_increment_history`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `staff_increment_history` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `staff_id` int(11) NOT NULL,
  `effective_date` date NOT NULL,
  `increment_amount` decimal(10,2) DEFAULT NULL COMMENT 'Fixed amount if type is Fixed',
  `increment_percentage` decimal(5,2) DEFAULT NULL COMMENT 'Percentage if type is Percentage',
  `increment_type` enum('Fixed','Percentage') DEFAULT 'Fixed' COMMENT 'Type of increment calculation',
  `merge_with` enum('basic','special_allowance') DEFAULT 'basic' COMMENT 'Where to merge after month 1',
  `is_recurring` tinyint(1) NOT NULL DEFAULT 1 COMMENT '1=Recurring increment (merges), 0=One-time bonus (no merge)',
  `new_basic_salary` decimal(10,2) DEFAULT NULL COMMENT 'Calculated new basic salary',
  `new_special_allowance` decimal(10,2) DEFAULT NULL COMMENT 'Calculated new special allowance',
  `approval_status` enum('Pending','Approved','Rejected') DEFAULT 'Pending' COMMENT 'HR approval status',
  `approved_by` int(11) DEFAULT NULL COMMENT 'Staff ID who approved',
  `approved_date` datetime DEFAULT NULL COMMENT 'Approval timestamp',
  `remarks` text DEFAULT NULL COMMENT 'Additional notes',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `approved_by` (`approved_by`),
  KEY `idx_staff_effective` (`staff_id`,`effective_date`),
  KEY `idx_staff_increment_effective` (`staff_id`,`effective_date`),
  KEY `idx_staff_increment_status` (`staff_id`,`approval_status`),
  KEY `idx_is_recurring` (`is_recurring`,`staff_id`),
  CONSTRAINT `staff_increment_history_ibfk_1` FOREIGN KEY (`staff_id`) REFERENCES `staff` (`id`) ON DELETE CASCADE,
  CONSTRAINT `staff_increment_history_ibfk_2` FOREIGN KEY (`approved_by`) REFERENCES `staff` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=151 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `staff_leave_balance_audit`
--

DROP TABLE IF EXISTS `staff_leave_balance_audit`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `staff_leave_balance_audit` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `balance_id` int(11) NOT NULL,
  `staff_id` int(11) NOT NULL,
  `leave_type_id` int(11) NOT NULL,
  `action_type` varchar(50) NOT NULL COMMENT 'CREDIT, DEBIT, LOP_ADJUSTMENT, LEAVE_APPLICATION, CORRECTION',
  `amount` decimal(10,2) NOT NULL,
  `balance_before` decimal(10,2) NOT NULL,
  `balance_after` decimal(10,2) NOT NULL,
  `reference_id` int(11) DEFAULT NULL,
  `reference_type` varchar(50) DEFAULT NULL,
  `performed_by` int(11) DEFAULT NULL,
  `reason` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_balance_id` (`balance_id`),
  KEY `idx_staff_id` (`staff_id`),
  KEY `idx_created_at` (`created_at`),
  CONSTRAINT `fk_slba_balance` FOREIGN KEY (`balance_id`) REFERENCES `staff_monthly_leave_balance` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=23716 DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `staff_leave_details`
--

DROP TABLE IF EXISTS `staff_leave_details`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `staff_leave_details` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `staff_id` int(11) NOT NULL,
  `leave_type_id` int(11) NOT NULL,
  `alloted_leave` varchar(100) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `FK_staff_leave_details_staff` (`staff_id`),
  KEY `FK_staff_leave_details_leave_types` (`leave_type_id`),
  CONSTRAINT `FK_staff_leave_details_leave_types` FOREIGN KEY (`leave_type_id`) REFERENCES `leave_types` (`id`) ON DELETE CASCADE,
  CONSTRAINT `FK_staff_leave_details_staff` FOREIGN KEY (`staff_id`) REFERENCES `staff` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=1598 DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `staff_leave_request`
--

DROP TABLE IF EXISTS `staff_leave_request`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `staff_leave_request` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `staff_id` int(11) NOT NULL,
  `leave_type_id` int(11) NOT NULL,
  `leave_from` date NOT NULL,
  `leave_to` date NOT NULL,
  `leave_days` decimal(5,2) NOT NULL DEFAULT 0.00,
  `leave_duration_type` varchar(20) NOT NULL DEFAULT 'full_day',
  `leave_direction` enum('credit','debit') NOT NULL DEFAULT 'credit',
  `employee_remark` varchar(200) DEFAULT NULL,
  `admin_remark` varchar(200) DEFAULT NULL,
  `approve_date` date DEFAULT NULL,
  `status` varchar(50) NOT NULL,
  `recommender_id` int(11) DEFAULT NULL,
  `recommender_status` enum('pending','recommended','rejected') NOT NULL DEFAULT 'pending',
  `recommender_remark` text DEFAULT NULL,
  `recommender_action_date` datetime DEFAULT NULL,
  `approver_id` int(11) DEFAULT NULL,
  `approver_status` enum('pending','approved','rejected') NOT NULL DEFAULT 'pending',
  `approver_remark` text DEFAULT NULL,
  `approver_action_date` datetime DEFAULT NULL,
  `applied_by` int(11) DEFAULT NULL,
  `document_file` varchar(200) NOT NULL,
  `date` date NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `alternative_teacher_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `FK_staff_leave_request_staff` (`staff_id`),
  KEY `FK_staff_leave_request_leave_types` (`leave_type_id`),
  KEY `applied_by` (`applied_by`),
  CONSTRAINT `FK_staff_leave_request_staff` FOREIGN KEY (`staff_id`) REFERENCES `staff` (`id`) ON DELETE CASCADE,
  CONSTRAINT `staff_leave_request_ibfk_1` FOREIGN KEY (`applied_by`) REFERENCES `staff` (`id`) ON DELETE CASCADE,
  CONSTRAINT `staff_leave_request_ibfk_2` FOREIGN KEY (`leave_type_id`) REFERENCES `leave_types` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=1213 DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `staff_monthly_leave_balance`
--

DROP TABLE IF EXISTS `staff_monthly_leave_balance`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `staff_monthly_leave_balance` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `staff_id` int(11) NOT NULL,
  `leave_type_id` int(11) NOT NULL,
  `year` int(11) NOT NULL,
  `month` int(11) NOT NULL,
  `opening_balance` decimal(10,2) NOT NULL DEFAULT 0.00,
  `earned_in_month` decimal(10,2) NOT NULL DEFAULT 0.00,
  `used_for_lop_adjustment` decimal(10,2) NOT NULL DEFAULT 0.00,
  `used_for_leave_application` decimal(10,2) NOT NULL DEFAULT 0.00,
  `other_deductions` decimal(10,2) NOT NULL DEFAULT 0.00 COMMENT 'Manual adjustments/corrections',
  `closing_balance` decimal(10,2) NOT NULL DEFAULT 0.00,
  `admin_adjustment` decimal(10,2) NOT NULL DEFAULT 0.00 COMMENT 'Super-admin manual override: added to opening_balance carry-forward on next payroll run',
  `payslip_id` int(11) DEFAULT NULL COMMENT 'Reference to staff_payslip',
  `notes` text DEFAULT NULL COMMENT 'Additional notes/remarks',
  `last_processed_date` datetime DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_staff_leave_month` (`staff_id`,`leave_type_id`,`year`,`month`),
  KEY `idx_staff_year_month` (`staff_id`,`year`,`month`),
  KEY `fk_smlb_leave_type` (`leave_type_id`),
  KEY `fk_smlb_payslip` (`payslip_id`),
  KEY `idx_closing_balance` (`closing_balance`),
  KEY `idx_year_month` (`year`,`month`),
  CONSTRAINT `fk_smlb_leave_type` FOREIGN KEY (`leave_type_id`) REFERENCES `leave_types` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_smlb_payslip` FOREIGN KEY (`payslip_id`) REFERENCES `staff_payslip` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_smlb_staff` FOREIGN KEY (`staff_id`) REFERENCES `staff` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=6527 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `staff_onetime_deductions`
--

DROP TABLE IF EXISTS `staff_onetime_deductions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `staff_onetime_deductions` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `staff_id` int(11) NOT NULL,
  `month` tinyint(3) unsigned NOT NULL,
  `year` smallint(5) unsigned NOT NULL,
  `deduction_type` varchar(50) NOT NULL,
  `amount` decimal(12,2) NOT NULL DEFAULT 0.00,
  `remarks` varchar(255) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `approval_status` enum('Pending','Approved','Rejected') NOT NULL DEFAULT 'Pending',
  `approved_by` int(11) DEFAULT NULL,
  `approved_at` datetime DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `updated_by` int(11) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_staff_month_year_type_active` (`staff_id`,`month`,`year`,`deduction_type`,`is_active`),
  KEY `idx_month_year` (`month`,`year`),
  KEY `idx_staff` (`staff_id`),
  KEY `idx_type` (`deduction_type`),
  KEY `idx_approval_status` (`approval_status`)
) ENGINE=InnoDB AUTO_INCREMENT=265 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `staff_payroll`
--

DROP TABLE IF EXISTS `staff_payroll`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `staff_payroll` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `basic_salary` int(11) NOT NULL,
  `pay_scale` varchar(200) NOT NULL,
  `grade` varchar(50) NOT NULL,
  `is_active` varchar(50) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `staff_payslip`
--

DROP TABLE IF EXISTS `staff_payslip`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `staff_payslip` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `staff_id` int(11) NOT NULL,
  `basic` float(10,2) NOT NULL,
  `total_allowance` float(10,2) NOT NULL,
  `total_deduction` float(10,2) NOT NULL,
  `leave_deduction` int(11) NOT NULL,
  `actual_lop_days` decimal(10,2) DEFAULT 0.00 COMMENT 'Original LOP days from attendance',
  `adjusted_lop_days` decimal(10,2) DEFAULT 0.00 COMMENT 'LOP days adjusted with paid leaves',
  `net_lop_days` decimal(10,2) DEFAULT 0.00 COMMENT 'Final LOP days after adjustment (used for salary deduction)',
  `tax` varchar(200) NOT NULL,
  `net_salary` float(10,2) NOT NULL,
  `status` varchar(100) NOT NULL,
  `month` varchar(200) NOT NULL,
  `year` varchar(200) NOT NULL,
  `payment_mode` varchar(200) NOT NULL,
  `payment_date` date NOT NULL,
  `remark` varchar(200) NOT NULL,
  `generated_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `epf_wage` decimal(10,2) DEFAULT 0.00 COMMENT 'EPF wage ceiling applied',
  `employee_epf` decimal(10,2) DEFAULT 0.00 COMMENT 'Employee EPF contribution (12%)',
  `employer_pf` decimal(10,2) DEFAULT 0.00 COMMENT 'Employer PF contribution (3.67%)',
  `employer_eps` decimal(10,2) DEFAULT 0.00 COMMENT 'Employer EPS contribution (8.33%, capped at 1250)',
  `employer_edli` decimal(10,2) DEFAULT 0.00,
  `employer_admin` decimal(10,2) DEFAULT 0.00,
  `esi_wage` decimal(10,2) DEFAULT 0.00,
  `employee_esi` decimal(10,2) DEFAULT 0.00,
  `employer_esi` decimal(10,2) DEFAULT 0.00,
  `tds` decimal(10,2) DEFAULT 0.00 COMMENT 'TDS deduction under new regime',
  `da` decimal(10,2) DEFAULT 0.00 COMMENT 'Dearness Allowance for EPF calculation',
  `tax_regime` varchar(10) DEFAULT 'new' COMMENT 'Tax regime: new or old',
  PRIMARY KEY (`id`),
  KEY `FK_staff_payslip_staff` (`staff_id`),
  CONSTRAINT `FK_staff_payslip_staff` FOREIGN KEY (`staff_id`) REFERENCES `staff` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=1504 DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `staff_permissions`
--

DROP TABLE IF EXISTS `staff_permissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `staff_permissions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `staff_id` int(11) NOT NULL,
  `date` date NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `staff_id` (`staff_id`),
  CONSTRAINT `staff_permissions_ibfk_1` FOREIGN KEY (`staff_id`) REFERENCES `staff` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `staff_rating`
--

DROP TABLE IF EXISTS `staff_rating`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `staff_rating` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `staff_id` int(11) NOT NULL,
  `comment` text NOT NULL,
  `rate` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `role` varchar(255) NOT NULL,
  `status` int(11) NOT NULL COMMENT '0 decline, 1 Approve',
  `entrydt` timestamp NOT NULL DEFAULT current_timestamp(),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `FK_staff_rating_staff` (`staff_id`),
  CONSTRAINT `FK_staff_rating_staff` FOREIGN KEY (`staff_id`) REFERENCES `staff` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `staff_roles`
--

DROP TABLE IF EXISTS `staff_roles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `staff_roles` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `role_id` int(11) DEFAULT NULL,
  `staff_id` int(11) DEFAULT NULL,
  `is_active` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `role_id` (`role_id`),
  KEY `staff_id` (`staff_id`),
  CONSTRAINT `FK_staff_roles_roles` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE,
  CONSTRAINT `FK_staff_roles_staff` FOREIGN KEY (`staff_id`) REFERENCES `staff` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=631 DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `staff_timeline`
--

DROP TABLE IF EXISTS `staff_timeline`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `staff_timeline` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `staff_id` int(11) NOT NULL,
  `title` varchar(200) NOT NULL,
  `timeline_date` date NOT NULL,
  `description` varchar(300) NOT NULL,
  `document` varchar(200) NOT NULL,
  `status` varchar(200) NOT NULL,
  `date` date NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `FK_staff_timeline_staff` (`staff_id`),
  CONSTRAINT `FK_staff_timeline_staff` FOREIGN KEY (`staff_id`) REFERENCES `staff` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `student_applied_discounts`
--

DROP TABLE IF EXISTS `student_applied_discounts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `student_applied_discounts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `student_fees_deposite_id` int(11) DEFAULT NULL,
  `student_fees_discount_id` int(11) DEFAULT NULL,
  `date` date DEFAULT NULL,
  `invoice_id` int(11) DEFAULT NULL,
  `sub_invoice_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `student_fees_deposite_id` (`student_fees_deposite_id`),
  KEY `student_fees_discount_id` (`student_fees_discount_id`),
  CONSTRAINT `student_applied_discounts_ibfk_1` FOREIGN KEY (`student_fees_deposite_id`) REFERENCES `student_fees_deposite` (`id`) ON DELETE CASCADE,
  CONSTRAINT `student_applied_discounts_ibfk_2` FOREIGN KEY (`student_fees_discount_id`) REFERENCES `student_fees_discounts` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=909 DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `student_applyleave`
--

DROP TABLE IF EXISTS `student_applyleave`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `student_applyleave` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `student_session_id` int(11) NOT NULL,
  `from_date` date NOT NULL,
  `to_date` date NOT NULL,
  `apply_date` date NOT NULL,
  `status` int(11) NOT NULL,
  `docs` varchar(200) DEFAULT NULL,
  `reason` text NOT NULL,
  `approve_by` int(11) DEFAULT NULL,
  `approve_date` date DEFAULT NULL,
  `request_type` int(11) NOT NULL COMMENT '0 student,1 staff',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `student_session_id` (`student_session_id`),
  KEY `approve_by` (`approve_by`),
  CONSTRAINT `student_applyleave_ibfk_1` FOREIGN KEY (`student_session_id`) REFERENCES `student_session` (`id`) ON DELETE CASCADE,
  CONSTRAINT `student_applyleave_ibfk_2` FOREIGN KEY (`approve_by`) REFERENCES `staff` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `student_attendence_schedules`
--

DROP TABLE IF EXISTS `student_attendence_schedules`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `student_attendence_schedules` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `attendence_type_id` int(11) DEFAULT NULL,
  `class_section_id` int(11) DEFAULT NULL,
  `entry_time_from` time DEFAULT NULL,
  `entry_time_to` time DEFAULT NULL,
  `total_institute_hour` time DEFAULT NULL,
  `is_active` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `student_attendences`
--

DROP TABLE IF EXISTS `student_attendences`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `student_attendences` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `student_session_id` int(11) DEFAULT NULL,
  `biometric_attendence` int(11) NOT NULL DEFAULT 0,
  `qrcode_attendance` int(11) NOT NULL DEFAULT 0,
  `date` date DEFAULT NULL,
  `attendence_type_id` int(11) DEFAULT NULL,
  `remark` varchar(200) NOT NULL,
  `biometric_device_data` text DEFAULT NULL,
  `user_agent` varchar(250) DEFAULT NULL,
  `in_time` time DEFAULT NULL,
  `out_time` time DEFAULT NULL,
  `is_active` varchar(255) DEFAULT 'no',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `student_session_id` (`student_session_id`),
  KEY `attendence_type_id` (`attendence_type_id`),
  CONSTRAINT `student_attendences_ibfk_1` FOREIGN KEY (`attendence_type_id`) REFERENCES `attendence_type` (`id`) ON DELETE CASCADE,
  CONSTRAINT `student_attendences_ibfk_2` FOREIGN KEY (`student_session_id`) REFERENCES `student_session` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `student_dashboard_settings`
--

DROP TABLE IF EXISTS `student_dashboard_settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `student_dashboard_settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(250) DEFAULT NULL,
  `short_code` varchar(255) NOT NULL,
  `is_student` int(11) DEFAULT NULL,
  `is_parent` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `student_doc`
--

DROP TABLE IF EXISTS `student_doc`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `student_doc` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `student_id` int(11) DEFAULT NULL,
  `title` varchar(200) DEFAULT NULL,
  `doc` varchar(200) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_student_id` (`student_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `student_edit_fields`
--

DROP TABLE IF EXISTS `student_edit_fields`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `student_edit_fields` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(250) DEFAULT NULL,
  `status` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=39 DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `student_educational_details`
--

DROP TABLE IF EXISTS `student_educational_details`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `student_educational_details` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `course` varchar(255) NOT NULL,
  `university` varchar(255) NOT NULL,
  `education_year` varchar(255) NOT NULL,
  `education_detail` varchar(255) NOT NULL,
  `student_id` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `student_fees`
--

DROP TABLE IF EXISTS `student_fees`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `student_fees` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `student_session_id` int(11) DEFAULT NULL,
  `feemaster_id` int(11) DEFAULT NULL,
  `amount` float(10,2) DEFAULT NULL,
  `amount_discount` float(10,2) NOT NULL,
  `amount_fine` float(10,2) NOT NULL DEFAULT 0.00,
  `description` text DEFAULT NULL,
  `date` date DEFAULT NULL,
  `payment_mode` varchar(50) NOT NULL,
  `is_active` varchar(255) DEFAULT 'no',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `feemaster_id` (`feemaster_id`),
  KEY `student_session_id` (`student_session_id`),
  CONSTRAINT `student_fees_ibfk_1` FOREIGN KEY (`feemaster_id`) REFERENCES `feemasters` (`id`) ON DELETE CASCADE,
  CONSTRAINT `student_fees_ibfk_2` FOREIGN KEY (`student_session_id`) REFERENCES `student_session` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `student_fees_deposite`
--

DROP TABLE IF EXISTS `student_fees_deposite`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `student_fees_deposite` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `student_fees_master_id` int(11) DEFAULT NULL,
  `fee_groups_feetype_id` int(11) DEFAULT NULL,
  `student_transport_fee_id` int(11) DEFAULT NULL,
  `amount_detail` text DEFAULT NULL,
  `old_bill_number` varchar(255) DEFAULT NULL,
  `old_bill_date` date DEFAULT NULL,
  `is_active` varchar(10) NOT NULL DEFAULT 'no',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `student_fees_master_id` (`student_fees_master_id`),
  KEY `fee_groups_feetype_id` (`fee_groups_feetype_id`),
  KEY `student_transport_fee_id` (`student_transport_fee_id`),
  CONSTRAINT `student_fees_deposite_ibfk_1` FOREIGN KEY (`student_transport_fee_id`) REFERENCES `student_transport_fees` (`id`) ON DELETE CASCADE,
  CONSTRAINT `student_fees_deposite_ibfk_2` FOREIGN KEY (`student_fees_master_id`) REFERENCES `student_fees_master` (`id`) ON DELETE CASCADE,
  CONSTRAINT `student_fees_deposite_ibfk_3` FOREIGN KEY (`fee_groups_feetype_id`) REFERENCES `fee_groups_feetype` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=5393 DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `student_fees_deposite_deleted`
--

DROP TABLE IF EXISTS `student_fees_deposite_deleted`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `student_fees_deposite_deleted` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `student_fees_deposite_id` int(11) NOT NULL COMMENT 'The original ID from the student_fees_deposite table',
  `student_session_id` int(11) NOT NULL COMMENT 'Links to the student and their session (class, section, year)',
  `fee_groups_feetype_id` int(11) DEFAULT NULL,
  `amount_detail` text DEFAULT NULL,
  `deleted_at` datetime NOT NULL,
  `deleted_by` int(11) DEFAULT NULL COMMENT 'Links to the staff.id who performed the deletion',
  `deletion_reason` text NOT NULL,
  PRIMARY KEY (`id`),
  KEY `student_session_id_idx` (`student_session_id`),
  KEY `deleted_by_idx` (`deleted_by`)
) ENGINE=InnoDB AUTO_INCREMENT=398 DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `student_fees_discounts`
--

DROP TABLE IF EXISTS `student_fees_discounts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `student_fees_discounts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `student_session_id` int(11) DEFAULT NULL,
  `fees_discount_id` int(11) DEFAULT NULL,
  `custom_amount` decimal(10,2) DEFAULT NULL,
  `status` varchar(20) DEFAULT 'assigned',
  `payment_id` varchar(50) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `is_active` varchar(10) NOT NULL DEFAULT 'no',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `student_session_id` (`student_session_id`),
  KEY `fees_discount_id` (`fees_discount_id`),
  CONSTRAINT `student_fees_discounts_ibfk_1` FOREIGN KEY (`fees_discount_id`) REFERENCES `fees_discounts` (`id`) ON DELETE CASCADE,
  CONSTRAINT `student_fees_discounts_ibfk_2` FOREIGN KEY (`student_session_id`) REFERENCES `student_session` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=813 DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `student_fees_master`
--

DROP TABLE IF EXISTS `student_fees_master`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `student_fees_master` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `is_system` int(11) NOT NULL DEFAULT 0,
  `student_session_id` int(11) DEFAULT NULL,
  `fee_session_group_id` int(11) DEFAULT NULL,
  `amount` float(10,2) DEFAULT 0.00,
  `is_active` varchar(10) NOT NULL DEFAULT 'no',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `student_session_id` (`student_session_id`),
  KEY `fee_session_group_id` (`fee_session_group_id`),
  CONSTRAINT `student_fees_master_ibfk_1` FOREIGN KEY (`fee_session_group_id`) REFERENCES `fee_session_groups` (`id`) ON DELETE CASCADE,
  CONSTRAINT `student_fees_master_ibfk_2` FOREIGN KEY (`student_session_id`) REFERENCES `student_session` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=5123 DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `student_fees_processing`
--

DROP TABLE IF EXISTS `student_fees_processing`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `student_fees_processing` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `gateway_ins_id` int(11) NOT NULL,
  `fee_category` varchar(255) NOT NULL,
  `student_fees_master_id` int(11) DEFAULT NULL,
  `fee_groups_feetype_id` int(11) DEFAULT NULL,
  `student_transport_fee_id` int(11) DEFAULT NULL,
  `amount_detail` text DEFAULT NULL,
  `is_active` varchar(10) NOT NULL DEFAULT 'no',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `student_fees_master_id` (`student_fees_master_id`),
  KEY `fee_groups_feetype_id` (`fee_groups_feetype_id`),
  KEY `student_transport_fee_id` (`student_transport_fee_id`),
  KEY `gateway_ins_id` (`gateway_ins_id`),
  CONSTRAINT `student_fees_processing_ibfk_1` FOREIGN KEY (`student_fees_master_id`) REFERENCES `student_fees_master` (`id`) ON DELETE CASCADE,
  CONSTRAINT `student_fees_processing_ibfk_2` FOREIGN KEY (`student_transport_fee_id`) REFERENCES `student_transport_fees` (`id`) ON DELETE CASCADE,
  CONSTRAINT `student_fees_processing_ibfk_3` FOREIGN KEY (`fee_groups_feetype_id`) REFERENCES `fee_groups_feetype` (`id`) ON DELETE CASCADE,
  CONSTRAINT `student_fees_processing_ibfk_4` FOREIGN KEY (`gateway_ins_id`) REFERENCES `gateway_ins` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `student_refrence`
--

DROP TABLE IF EXISTS `student_refrence`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `student_refrence` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `relation` varchar(255) NOT NULL,
  `age` varchar(255) NOT NULL,
  `profession` varchar(255) NOT NULL,
  `contact` varchar(255) NOT NULL,
  `student_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `student_session`
--

DROP TABLE IF EXISTS `student_session`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `student_session` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `session_id` int(11) DEFAULT NULL,
  `student_id` int(11) DEFAULT NULL,
  `class_id` int(11) DEFAULT NULL,
  `section_id` int(11) DEFAULT NULL,
  `department_id` int(11) DEFAULT NULL,
  `hostel_room_id` int(11) DEFAULT NULL,
  `vehroute_id` int(11) DEFAULT NULL,
  `route_pickup_point_id` int(11) DEFAULT NULL,
  `transport_fees` float(10,2) NOT NULL DEFAULT 0.00,
  `fees_discount` float(10,2) NOT NULL DEFAULT 0.00,
  `is_leave` int(11) NOT NULL DEFAULT 0,
  `is_active` varchar(255) DEFAULT 'no',
  `is_alumni` int(11) NOT NULL,
  `default_login` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `session_id` (`session_id`),
  KEY `student_id` (`student_id`),
  KEY `class_id` (`class_id`),
  KEY `section_id` (`section_id`),
  KEY `student_session_ibfk_5` (`vehroute_id`),
  KEY `hostel_room_id` (`hostel_room_id`),
  KEY `student_session_ibfk_6` (`route_pickup_point_id`),
  CONSTRAINT `student_session_ibfk_1` FOREIGN KEY (`session_id`) REFERENCES `sessions` (`id`) ON DELETE CASCADE,
  CONSTRAINT `student_session_ibfk_2` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE,
  CONSTRAINT `student_session_ibfk_3` FOREIGN KEY (`class_id`) REFERENCES `classes` (`id`) ON DELETE CASCADE,
  CONSTRAINT `student_session_ibfk_4` FOREIGN KEY (`section_id`) REFERENCES `sections` (`id`) ON DELETE CASCADE,
  CONSTRAINT `student_session_ibfk_5` FOREIGN KEY (`vehroute_id`) REFERENCES `vehicle_routes` (`id`) ON DELETE SET NULL,
  CONSTRAINT `student_session_ibfk_6` FOREIGN KEY (`route_pickup_point_id`) REFERENCES `route_pickup_point` (`id`) ON DELETE SET NULL,
  CONSTRAINT `student_session_ibfk_7` FOREIGN KEY (`hostel_room_id`) REFERENCES `hostel_rooms` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=3864 DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `student_skills_detail`
--

DROP TABLE IF EXISTS `student_skills_detail`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `student_skills_detail` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `skill_category` varchar(255) NOT NULL,
  `skill_detail` varchar(255) NOT NULL,
  `student_id` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `student_subject_attendances`
--

DROP TABLE IF EXISTS `student_subject_attendances`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `student_subject_attendances` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `student_session_id` int(11) DEFAULT NULL,
  `subject_timetable_id` int(11) DEFAULT NULL,
  `attendence_type_id` int(11) DEFAULT NULL,
  `date` date DEFAULT NULL,
  `remark` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `attendence_type_id` (`attendence_type_id`),
  KEY `student_session_id` (`student_session_id`),
  KEY `subject_timetable_id` (`subject_timetable_id`),
  CONSTRAINT `student_subject_attendances_ibfk_1` FOREIGN KEY (`attendence_type_id`) REFERENCES `attendence_type` (`id`) ON DELETE CASCADE,
  CONSTRAINT `student_subject_attendances_ibfk_2` FOREIGN KEY (`student_session_id`) REFERENCES `student_session` (`id`) ON DELETE CASCADE,
  CONSTRAINT `student_subject_attendances_ibfk_3` FOREIGN KEY (`subject_timetable_id`) REFERENCES `subject_timetable` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `student_timeline`
--

DROP TABLE IF EXISTS `student_timeline`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `student_timeline` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `student_id` int(11) NOT NULL,
  `title` varchar(200) NOT NULL,
  `timeline_date` date NOT NULL,
  `description` text NOT NULL,
  `document` varchar(200) DEFAULT NULL,
  `status` varchar(200) NOT NULL,
  `created_student_id` int(11) NOT NULL,
  `date` date NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `student_id` (`student_id`),
  CONSTRAINT `student_timeline_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `student_transport_fees`
--

DROP TABLE IF EXISTS `student_transport_fees`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `student_transport_fees` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `transport_feemaster_id` int(11) NOT NULL,
  `student_session_id` int(11) NOT NULL,
  `route_pickup_point_id` int(11) NOT NULL,
  `generated_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `student_session_id` (`student_session_id`),
  KEY `route_pickup_point_id` (`route_pickup_point_id`),
  KEY `transport_feemaster_id` (`transport_feemaster_id`),
  CONSTRAINT `student_transport_fees_ibfk_1` FOREIGN KEY (`student_session_id`) REFERENCES `student_session` (`id`) ON DELETE CASCADE,
  CONSTRAINT `student_transport_fees_ibfk_2` FOREIGN KEY (`route_pickup_point_id`) REFERENCES `route_pickup_point` (`id`) ON DELETE CASCADE,
  CONSTRAINT `student_transport_fees_ibfk_3` FOREIGN KEY (`transport_feemaster_id`) REFERENCES `transport_feemaster` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=161 DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `student_work_experience`
--

DROP TABLE IF EXISTS `student_work_experience`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `student_work_experience` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `institute` text NOT NULL,
  `designation` text NOT NULL,
  `year` varchar(255) NOT NULL,
  `location` text NOT NULL,
  `detail` text NOT NULL,
  `student_id` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `students`
--

DROP TABLE IF EXISTS `students`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `students` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `register_no` varchar(50) DEFAULT NULL,
  `regulation_id` varchar(50) DEFAULT NULL,
  `emis_num` varchar(50) DEFAULT NULL,
  `hsc_reg_no` varchar(50) DEFAULT NULL,
  `ug_reg_no` varchar(50) DEFAULT NULL,
  `parent_id` int(11) NOT NULL DEFAULT 0,
  `advance_balance` decimal(10,2) NOT NULL DEFAULT 0.00,
  `admission_no` varchar(100) DEFAULT NULL,
  `roll_no` varchar(100) DEFAULT NULL,
  `admission_date` date DEFAULT NULL,
  `firstname` varchar(100) DEFAULT NULL,
  `middlename` varchar(255) DEFAULT NULL,
  `lastname` varchar(100) DEFAULT NULL,
  `rte` varchar(20) DEFAULT NULL,
  `image` varchar(100) DEFAULT NULL,
  `mobileno` varchar(100) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `state` varchar(100) DEFAULT NULL,
  `city` varchar(100) DEFAULT NULL,
  `pincode` varchar(100) DEFAULT NULL,
  `religion` varchar(100) DEFAULT NULL,
  `cast` varchar(50) DEFAULT NULL,
  `dob` date DEFAULT NULL,
  `gender` varchar(100) DEFAULT NULL,
  `current_address` text DEFAULT NULL,
  `permanent_address` text DEFAULT NULL,
  `category_id` int(11) DEFAULT NULL,
  `school_house_id` int(11) DEFAULT NULL,
  `blood_group` varchar(200) NOT NULL DEFAULT '',
  `hostel_room_id` int(11) DEFAULT NULL,
  `adhar_no` varchar(100) DEFAULT NULL,
  `abc_id` varchar(50) DEFAULT NULL,
  `father_adhar_no` varchar(12) DEFAULT NULL,
  `mother_adhar_no` varchar(12) DEFAULT NULL,
  `migration_cert_num` varchar(50) DEFAULT NULL,
  `medium` varchar(50) DEFAULT NULL,
  `samagra_id` varchar(100) DEFAULT NULL,
  `bank_account_no` varchar(100) DEFAULT NULL,
  `bank_name` varchar(100) DEFAULT NULL,
  `ifsc_code` varchar(100) DEFAULT NULL,
  `guardian_is` varchar(100) NOT NULL DEFAULT '',
  `father_name` varchar(100) DEFAULT NULL,
  `father_phone` varchar(100) DEFAULT NULL,
  `father_occupation` varchar(100) DEFAULT NULL,
  `mother_name` varchar(100) DEFAULT NULL,
  `mother_phone` varchar(100) DEFAULT NULL,
  `mother_occupation` varchar(100) DEFAULT NULL,
  `guardian_name` varchar(100) DEFAULT NULL,
  `guardian_relation` varchar(100) DEFAULT NULL,
  `guardian_phone` varchar(100) DEFAULT NULL,
  `guardian_occupation` varchar(150) NOT NULL DEFAULT '',
  `guardian_address` text DEFAULT NULL,
  `guardian_email` varchar(100) DEFAULT NULL,
  `father_pic` varchar(200) NOT NULL DEFAULT '',
  `mother_pic` varchar(200) NOT NULL DEFAULT '',
  `guardian_pic` varchar(200) NOT NULL DEFAULT '',
  `is_active` varchar(255) DEFAULT 'yes',
  `previous_school` text DEFAULT NULL,
  `height` varchar(100) NOT NULL DEFAULT '',
  `weight` varchar(100) NOT NULL DEFAULT '',
  `measurement_date` date DEFAULT NULL,
  `dis_reason` int(11) NOT NULL DEFAULT 0,
  `note` varchar(200) DEFAULT NULL,
  `dis_note` text NOT NULL,
  `about` text DEFAULT NULL,
  `designation` varchar(255) DEFAULT NULL,
  `app_key` text DEFAULT NULL,
  `parent_app_key` text DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `disable_at` date DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_admission_no` (`admission_no`),
  KEY `idx_roll_no` (`roll_no`),
  KEY `idx_mobileno` (`mobileno`),
  KEY `idx_email` (`email`),
  KEY `idx_firstname` (`firstname`)
) ENGINE=InnoDB AUTO_INCREMENT=3864 DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `subject_group_class_sections`
--

DROP TABLE IF EXISTS `subject_group_class_sections`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `subject_group_class_sections` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `subject_group_id` int(11) DEFAULT NULL,
  `class_section_id` int(11) DEFAULT NULL,
  `session_id` int(11) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `is_active` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `class_section_id` (`class_section_id`),
  KEY `subject_group_id` (`subject_group_id`),
  KEY `session_id` (`session_id`),
  CONSTRAINT `subject_group_class_sections_ibfk_1` FOREIGN KEY (`class_section_id`) REFERENCES `class_sections` (`id`) ON DELETE CASCADE,
  CONSTRAINT `subject_group_class_sections_ibfk_2` FOREIGN KEY (`subject_group_id`) REFERENCES `subject_groups` (`id`) ON DELETE CASCADE,
  CONSTRAINT `subject_group_class_sections_ibfk_3` FOREIGN KEY (`session_id`) REFERENCES `sessions` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `subject_group_subjects`
--

DROP TABLE IF EXISTS `subject_group_subjects`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `subject_group_subjects` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `subject_group_id` int(11) DEFAULT NULL,
  `session_id` int(11) DEFAULT NULL,
  `subject_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `subject_group_id` (`subject_group_id`),
  KEY `session_id` (`session_id`),
  KEY `subject_id` (`subject_id`),
  CONSTRAINT `subject_group_subjects_ibfk_1` FOREIGN KEY (`subject_group_id`) REFERENCES `subject_groups` (`id`) ON DELETE CASCADE,
  CONSTRAINT `subject_group_subjects_ibfk_2` FOREIGN KEY (`session_id`) REFERENCES `sessions` (`id`) ON DELETE CASCADE,
  CONSTRAINT `subject_group_subjects_ibfk_3` FOREIGN KEY (`subject_id`) REFERENCES `subjects` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=28 DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `subject_groups`
--

DROP TABLE IF EXISTS `subject_groups`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `subject_groups` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(250) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `department_id` int(11) DEFAULT NULL,
  `session_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `class_id` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `session_id` (`session_id`),
  CONSTRAINT `subject_groups_ibfk_1` FOREIGN KEY (`session_id`) REFERENCES `sessions` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `subject_syllabus`
--

DROP TABLE IF EXISTS `subject_syllabus`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `subject_syllabus` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `topic_id` int(11) NOT NULL,
  `session_id` int(11) NOT NULL,
  `created_by` int(11) NOT NULL,
  `created_for` int(11) NOT NULL,
  `date` date NOT NULL,
  `time_from` varchar(255) NOT NULL,
  `time_to` varchar(255) NOT NULL,
  `presentation` text NOT NULL,
  `attachment` text NOT NULL,
  `lacture_youtube_url` varchar(255) NOT NULL,
  `lacture_video` varchar(255) NOT NULL,
  `sub_topic` text NOT NULL,
  `teaching_method` text NOT NULL,
  `general_objectives` text NOT NULL,
  `previous_knowledge` text NOT NULL,
  `comprehensive_questions` text NOT NULL,
  `status` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `topic_id` (`topic_id`),
  KEY `session_id` (`session_id`),
  KEY `created_by` (`created_by`),
  KEY `created_for` (`created_for`),
  CONSTRAINT `subject_syllabus_ibfk_1` FOREIGN KEY (`topic_id`) REFERENCES `topic` (`id`) ON DELETE CASCADE,
  CONSTRAINT `subject_syllabus_ibfk_2` FOREIGN KEY (`session_id`) REFERENCES `sessions` (`id`) ON DELETE CASCADE,
  CONSTRAINT `subject_syllabus_ibfk_3` FOREIGN KEY (`created_by`) REFERENCES `staff` (`id`) ON DELETE CASCADE,
  CONSTRAINT `subject_syllabus_ibfk_4` FOREIGN KEY (`created_for`) REFERENCES `staff` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `subject_timetable`
--

DROP TABLE IF EXISTS `subject_timetable`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `subject_timetable` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `session_id` int(11) DEFAULT NULL,
  `class_id` int(11) DEFAULT NULL,
  `section_id` int(11) DEFAULT NULL,
  `subject_group_id` int(11) DEFAULT NULL,
  `subject_group_subject_id` int(11) DEFAULT NULL,
  `staff_id` int(11) DEFAULT NULL,
  `day` varchar(20) DEFAULT NULL,
  `time_from` varchar(20) DEFAULT NULL,
  `time_to` varchar(20) DEFAULT NULL,
  `start_time` time DEFAULT NULL,
  `end_time` time DEFAULT NULL,
  `room_no` varchar(20) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `class_id` (`class_id`),
  KEY `section_id` (`section_id`),
  KEY `subject_group_id` (`subject_group_id`),
  KEY `subject_group_subject_id` (`subject_group_subject_id`),
  KEY `staff_id` (`staff_id`),
  KEY `session_id` (`session_id`),
  CONSTRAINT `subject_timetable_ibfk_1` FOREIGN KEY (`class_id`) REFERENCES `classes` (`id`) ON DELETE CASCADE,
  CONSTRAINT `subject_timetable_ibfk_2` FOREIGN KEY (`section_id`) REFERENCES `sections` (`id`) ON DELETE CASCADE,
  CONSTRAINT `subject_timetable_ibfk_3` FOREIGN KEY (`subject_group_id`) REFERENCES `subject_groups` (`id`) ON DELETE CASCADE,
  CONSTRAINT `subject_timetable_ibfk_4` FOREIGN KEY (`subject_group_subject_id`) REFERENCES `subject_group_subjects` (`id`) ON DELETE CASCADE,
  CONSTRAINT `subject_timetable_ibfk_5` FOREIGN KEY (`staff_id`) REFERENCES `staff` (`id`) ON DELETE CASCADE,
  CONSTRAINT `subject_timetable_ibfk_6` FOREIGN KEY (`session_id`) REFERENCES `sessions` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=457 DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `subjects`
--

DROP TABLE IF EXISTS `subjects`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `subjects` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) DEFAULT NULL,
  `code` varchar(100) NOT NULL,
  `type` varchar(100) NOT NULL,
  `department_id` int(11) DEFAULT NULL,
  `teacher_id` text DEFAULT NULL,
  `is_active` varchar(255) DEFAULT 'no',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_name` (`name`),
  KEY `idx_code` (`code`)
) ENGINE=InnoDB AUTO_INCREMENT=28 DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `submit_assignment`
--

DROP TABLE IF EXISTS `submit_assignment`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `submit_assignment` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `homework_id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `message` text NOT NULL,
  `docs` varchar(225) NOT NULL,
  `file_name` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `student_id` (`student_id`),
  KEY `homework_id` (`homework_id`),
  CONSTRAINT `submit_assignment_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE,
  CONSTRAINT `submit_assignment_ibfk_2` FOREIGN KEY (`homework_id`) REFERENCES `homework` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `template_admitcards`
--

DROP TABLE IF EXISTS `template_admitcards`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `template_admitcards` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `template` varchar(250) DEFAULT NULL,
  `heading` text DEFAULT NULL,
  `title` text DEFAULT NULL,
  `left_logo` varchar(200) DEFAULT NULL,
  `right_logo` varchar(200) DEFAULT NULL,
  `exam_name` varchar(200) DEFAULT NULL,
  `school_name` varchar(200) DEFAULT NULL,
  `exam_center` varchar(200) DEFAULT NULL,
  `sign` varchar(200) DEFAULT NULL,
  `background_img` varchar(200) DEFAULT NULL,
  `is_name` int(11) NOT NULL DEFAULT 1,
  `is_father_name` int(11) NOT NULL DEFAULT 1,
  `is_mother_name` int(11) NOT NULL DEFAULT 1,
  `is_dob` int(11) NOT NULL DEFAULT 1,
  `is_admission_no` int(11) NOT NULL DEFAULT 1,
  `is_roll_no` int(11) NOT NULL DEFAULT 1,
  `is_address` int(11) NOT NULL DEFAULT 1,
  `is_gender` int(11) NOT NULL DEFAULT 1,
  `is_photo` int(11) NOT NULL,
  `is_class` int(11) NOT NULL DEFAULT 0,
  `is_section` int(11) NOT NULL DEFAULT 0,
  `is_active` int(11) DEFAULT 0,
  `content_footer` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `template_marksheets`
--

DROP TABLE IF EXISTS `template_marksheets`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `template_marksheets` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `header_image` varchar(200) DEFAULT NULL,
  `template` varchar(200) DEFAULT NULL,
  `heading` text DEFAULT NULL,
  `title` text DEFAULT NULL,
  `left_logo` varchar(200) DEFAULT NULL,
  `right_logo` varchar(200) DEFAULT NULL,
  `exam_name` varchar(200) DEFAULT NULL,
  `school_name` varchar(200) DEFAULT NULL,
  `exam_center` varchar(200) DEFAULT NULL,
  `left_sign` varchar(200) DEFAULT NULL,
  `middle_sign` varchar(200) DEFAULT NULL,
  `right_sign` varchar(200) DEFAULT NULL,
  `exam_session` int(11) DEFAULT 1,
  `is_name` int(11) DEFAULT 1,
  `is_father_name` int(11) DEFAULT 1,
  `is_mother_name` int(11) DEFAULT 1,
  `is_dob` int(11) DEFAULT 1,
  `is_admission_no` int(11) DEFAULT 1,
  `is_roll_no` int(11) DEFAULT 1,
  `is_photo` int(11) DEFAULT 1,
  `is_division` int(11) NOT NULL DEFAULT 1,
  `is_rank` int(11) NOT NULL DEFAULT 0,
  `is_customfield` int(11) NOT NULL,
  `background_img` varchar(200) DEFAULT NULL,
  `date` varchar(20) DEFAULT NULL,
  `is_class` int(11) NOT NULL DEFAULT 0,
  `is_teacher_remark` int(11) NOT NULL DEFAULT 1,
  `is_section` int(11) NOT NULL DEFAULT 0,
  `content` text DEFAULT NULL,
  `content_footer` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `topic`
--

DROP TABLE IF EXISTS `topic`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `topic` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `session_id` int(11) NOT NULL,
  `lesson_id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `status` int(11) NOT NULL,
  `complete_date` date DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `session_id` (`session_id`),
  KEY `lesson_id` (`lesson_id`),
  CONSTRAINT `topic_ibfk_1` FOREIGN KEY (`session_id`) REFERENCES `sessions` (`id`) ON DELETE CASCADE,
  CONSTRAINT `topic_ibfk_2` FOREIGN KEY (`lesson_id`) REFERENCES `lesson` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `transport_feemaster`
--

DROP TABLE IF EXISTS `transport_feemaster`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `transport_feemaster` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `session_id` int(11) NOT NULL,
  `month` varchar(50) DEFAULT NULL,
  `due_date` date DEFAULT NULL,
  `fine_amount` float(10,2) DEFAULT 0.00,
  `fine_type` varchar(50) DEFAULT NULL,
  `fine_percentage` float(10,2) DEFAULT 0.00,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `session_id` (`session_id`),
  CONSTRAINT `transport_feemaster_ibfk_1` FOREIGN KEY (`session_id`) REFERENCES `sessions` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `transport_route`
--

DROP TABLE IF EXISTS `transport_route`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `transport_route` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `route_title` varchar(100) DEFAULT NULL,
  `no_of_vehicle` int(11) DEFAULT NULL,
  `note` text DEFAULT NULL,
  `is_active` varchar(255) DEFAULT 'no',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `upload_contents`
--

DROP TABLE IF EXISTS `upload_contents`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `upload_contents` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `content_type_id` int(11) NOT NULL,
  `image` varchar(300) DEFAULT NULL,
  `thumb_path` varchar(300) DEFAULT NULL,
  `dir_path` varchar(300) DEFAULT NULL,
  `real_name` text NOT NULL,
  `img_name` varchar(300) DEFAULT NULL,
  `thumb_name` varchar(300) DEFAULT NULL,
  `file_type` varchar(100) NOT NULL,
  `mime_type` text NOT NULL,
  `file_size` varchar(100) NOT NULL,
  `vid_url` text NOT NULL,
  `vid_title` varchar(250) NOT NULL,
  `upload_by` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `upload_by` (`upload_by`),
  KEY `upload_contents_ibfk_2` (`content_type_id`),
  KEY `idx_file_type` (`file_type`),
  CONSTRAINT `upload_contents_ibfk_1` FOREIGN KEY (`upload_by`) REFERENCES `staff` (`id`) ON DELETE CASCADE,
  CONSTRAINT `upload_contents_ibfk_2` FOREIGN KEY (`content_type_id`) REFERENCES `content_types` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `userlog`
--

DROP TABLE IF EXISTS `userlog`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `userlog` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user` varchar(100) DEFAULT NULL,
  `role` varchar(100) DEFAULT NULL,
  `class_section_id` int(11) DEFAULT NULL,
  `ipaddress` varchar(100) DEFAULT NULL,
  `user_agent` varchar(500) DEFAULT NULL,
  `login_datetime` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `class_section_id` (`class_section_id`),
  CONSTRAINT `userlog_ibfk_1` FOREIGN KEY (`class_section_id`) REFERENCES `class_sections` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=4707 DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `username` varchar(50) DEFAULT NULL,
  `password` varchar(50) DEFAULT NULL,
  `childs` text NOT NULL,
  `role` varchar(30) NOT NULL,
  `lang_id` int(11) NOT NULL,
  `currency_id` int(11) DEFAULT 0,
  `verification_code` varchar(200) NOT NULL,
  `is_active` varchar(255) DEFAULT 'yes',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=7727 DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `users_authentication`
--

DROP TABLE IF EXISTS `users_authentication`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users_authentication` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `users_id` int(11) NOT NULL,
  `token` varchar(255) NOT NULL,
  `staff_id` int(11) DEFAULT NULL,
  `expired_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Temporary table structure for view `v_staff_current_leave_balance`
--

DROP TABLE IF EXISTS `v_staff_current_leave_balance`;
/*!50001 DROP VIEW IF EXISTS `v_staff_current_leave_balance`*/;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
/*!50001 CREATE VIEW `v_staff_current_leave_balance` AS SELECT
 1 AS `staff_id`,
  1 AS `staff_name`,
  1 AS `employee_id`,
  1 AS `leave_type_name`,
  1 AS `is_lop`,
  1 AS `leave_type_id`,
  1 AS `year`,
  1 AS `month`,
  1 AS `opening_balance`,
  1 AS `earned_in_month`,
  1 AS `used_for_lop_adjustment`,
  1 AS `used_for_leave_application`,
  1 AS `other_deductions`,
  1 AS `closing_balance`,
  1 AS `payslip_id`,
  1 AS `last_processed_date` */;
SET character_set_client = @saved_cs_client;

--
-- Temporary table structure for view `v_staff_yearly_leave_summary`
--

DROP TABLE IF EXISTS `v_staff_yearly_leave_summary`;
/*!50001 DROP VIEW IF EXISTS `v_staff_yearly_leave_summary`*/;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
/*!50001 CREATE VIEW `v_staff_yearly_leave_summary` AS SELECT
 1 AS `staff_id`,
  1 AS `staff_name`,
  1 AS `employee_id`,
  1 AS `leave_type_id`,
  1 AS `leave_type_name`,
  1 AS `is_lop`,
  1 AS `year`,
  1 AS `total_earned`,
  1 AS `total_used_for_lop`,
  1 AS `total_used_for_leave`,
  1 AS `total_other_deductions`,
  1 AS `current_balance` */;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `vehicle_routes`
--

DROP TABLE IF EXISTS `vehicle_routes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `vehicle_routes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `route_id` int(11) DEFAULT NULL,
  `vehicle_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `route_id` (`route_id`),
  KEY `vehicle_id` (`vehicle_id`),
  CONSTRAINT `vehicle_routes_ibfk_1` FOREIGN KEY (`route_id`) REFERENCES `transport_route` (`id`) ON DELETE CASCADE,
  CONSTRAINT `vehicle_routes_ibfk_2` FOREIGN KEY (`vehicle_id`) REFERENCES `vehicles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `vehicles`
--

DROP TABLE IF EXISTS `vehicles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `vehicles` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `vehicle_no` varchar(20) DEFAULT NULL,
  `vehicle_model` varchar(100) NOT NULL DEFAULT 'None',
  `vehicle_photo` varchar(255) DEFAULT NULL,
  `manufacture_year` varchar(4) DEFAULT NULL,
  `registration_number` varchar(50) NOT NULL,
  `chasis_number` varchar(100) NOT NULL,
  `engine_number` varchar(100) DEFAULT NULL,
  `max_seating_capacity` varchar(255) NOT NULL,
  `driver_name` varchar(50) DEFAULT NULL,
  `driver_licence` varchar(50) NOT NULL DEFAULT 'None',
  `driver_contact` varchar(20) DEFAULT NULL,
  `note` text DEFAULT NULL,
  `fc_validity_start` date DEFAULT NULL,
  `fc_validity_end` date DEFAULT NULL,
  `insurance_start` date DEFAULT NULL,
  `insurance_end` date DEFAULT NULL,
  `permit_expiry_start` date DEFAULT NULL,
  `permit_expiry_end` date DEFAULT NULL,
  `road_tax_start` date DEFAULT NULL,
  `road_tax_end` date DEFAULT NULL,
  `pollution_cert_start` date DEFAULT NULL,
  `pollution_cert_end` date DEFAULT NULL,
  `green_tax_start` date DEFAULT NULL,
  `green_tax_end` date DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_vehicle_no` (`vehicle_no`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `video_tutorial`
--

DROP TABLE IF EXISTS `video_tutorial`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `video_tutorial` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(100) NOT NULL,
  `vid_title` text DEFAULT NULL,
  `description` text NOT NULL,
  `thumb_path` varchar(500) DEFAULT NULL,
  `dir_path` varchar(500) DEFAULT NULL,
  `img_name` varchar(300) NOT NULL,
  `thumb_name` varchar(300) NOT NULL,
  `video_link` varchar(100) NOT NULL,
  `created_by` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `created_by` (`created_by`),
  KEY `idx_title` (`title`),
  CONSTRAINT `video_tutorial_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `staff` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `video_tutorial_class_sections`
--

DROP TABLE IF EXISTS `video_tutorial_class_sections`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `video_tutorial_class_sections` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `video_tutorial_id` int(11) NOT NULL,
  `class_section_id` int(11) NOT NULL,
  `created_date` date NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `class_section_id` (`class_section_id`),
  KEY `video_tutorial_id` (`video_tutorial_id`),
  CONSTRAINT `video_tutorial_class_sections_ibfk_1` FOREIGN KEY (`class_section_id`) REFERENCES `class_sections` (`id`) ON DELETE CASCADE,
  CONSTRAINT `video_tutorial_class_sections_ibfk_2` FOREIGN KEY (`video_tutorial_id`) REFERENCES `video_tutorial` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `visitors_book`
--

DROP TABLE IF EXISTS `visitors_book`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `visitors_book` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `staff_id` int(11) DEFAULT NULL,
  `student_session_id` int(11) DEFAULT NULL,
  `source` varchar(100) DEFAULT NULL,
  `purpose` varchar(255) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `contact` varchar(12) NOT NULL,
  `id_proof` varchar(50) NOT NULL,
  `no_of_people` int(11) NOT NULL,
  `date` date NOT NULL,
  `in_time` varchar(20) NOT NULL,
  `out_time` varchar(20) NOT NULL,
  `note` text NOT NULL,
  `image` varchar(100) DEFAULT NULL,
  `meeting_with` varchar(20) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `staff_id` (`staff_id`),
  KEY `student_session_id` (`student_session_id`),
  CONSTRAINT `visitors_book_ibfk_1` FOREIGN KEY (`staff_id`) REFERENCES `staff` (`id`) ON DELETE CASCADE,
  CONSTRAINT `visitors_book_ibfk_2` FOREIGN KEY (`student_session_id`) REFERENCES `student_session` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `visitors_purpose`
--

DROP TABLE IF EXISTS `visitors_purpose`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `visitors_purpose` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `visitors_purpose` varchar(100) NOT NULL,
  `description` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `whatsapp_config`
--

DROP TABLE IF EXISTS `whatsapp_config`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `whatsapp_config` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `type` varchar(50) NOT NULL,
  `language` varchar(50) DEFAULT NULL,
  `name` varchar(100) NOT NULL,
  `api_id` varchar(100) NOT NULL,
  `authkey` text NOT NULL,
  `senderid` varchar(100) NOT NULL,
  `contact` text DEFAULT NULL,
  `username` varchar(150) DEFAULT NULL,
  `password` varchar(150) DEFAULT NULL,
  `is_active` varchar(255) DEFAULT 'disabled',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `waba_id` varchar(100) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `whatsapp_message_log`
--

DROP TABLE IF EXISTS `whatsapp_message_log`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `whatsapp_message_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `notification_type` varchar(100) DEFAULT NULL,
  `triggered_by` int(11) DEFAULT NULL,
  `is_bulk` tinyint(1) NOT NULL DEFAULT 0,
  `recipient` varchar(20) DEFAULT NULL,
  `recipient_group` varchar(200) DEFAULT NULL,
  `recipient_count` int(11) NOT NULL DEFAULT 1,
  `sent_at` timestamp NULL DEFAULT current_timestamp(),
  `month` tinyint(4) DEFAULT NULL,
  `year` smallint(6) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `zoom_settings`
--

DROP TABLE IF EXISTS `zoom_settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `zoom_settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `zoom_api_key` varchar(200) DEFAULT NULL,
  `zoom_api_secret` varchar(200) DEFAULT NULL,
  `use_teacher_api` int(11) DEFAULT 0,
  `use_zoom_app` int(11) DEFAULT 1,
  `use_zoom_app_user` int(11) DEFAULT 1,
  `parent_live_class` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Final view structure for view `v_staff_current_leave_balance`
--

/*!50001 DROP VIEW IF EXISTS `v_staff_current_leave_balance`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8mb4 */;
/*!50001 SET character_set_results     = utf8mb4 */;
/*!50001 SET collation_connection      = utf8mb4_general_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`root`@`localhost` SQL SECURITY DEFINER */
/*!50001 VIEW `v_staff_current_leave_balance` AS select `smlb`.`staff_id` AS `staff_id`,`s`.`name` AS `staff_name`,`s`.`employee_id` AS `employee_id`,`lt`.`type` AS `leave_type_name`,`lt`.`is_lop` AS `is_lop`,`smlb`.`leave_type_id` AS `leave_type_id`,`smlb`.`year` AS `year`,`smlb`.`month` AS `month`,`smlb`.`opening_balance` AS `opening_balance`,`smlb`.`earned_in_month` AS `earned_in_month`,`smlb`.`used_for_lop_adjustment` AS `used_for_lop_adjustment`,`smlb`.`used_for_leave_application` AS `used_for_leave_application`,`smlb`.`other_deductions` AS `other_deductions`,`smlb`.`closing_balance` AS `closing_balance`,`smlb`.`payslip_id` AS `payslip_id`,`smlb`.`last_processed_date` AS `last_processed_date` from ((`staff_monthly_leave_balance` `smlb` join `staff` `s` on(`smlb`.`staff_id` = `s`.`id`)) join `leave_types` `lt` on(`smlb`.`leave_type_id` = `lt`.`id`)) where `smlb`.`year` = year(curdate()) and `smlb`.`month` = month(curdate()) */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;

--
-- Final view structure for view `v_staff_yearly_leave_summary`
--

/*!50001 DROP VIEW IF EXISTS `v_staff_yearly_leave_summary`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8mb4 */;
/*!50001 SET character_set_results     = utf8mb4 */;
/*!50001 SET collation_connection      = utf8mb4_general_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`root`@`localhost` SQL SECURITY DEFINER */
/*!50001 VIEW `v_staff_yearly_leave_summary` AS select `smlb`.`staff_id` AS `staff_id`,`s`.`name` AS `staff_name`,`s`.`employee_id` AS `employee_id`,`smlb`.`leave_type_id` AS `leave_type_id`,`lt`.`type` AS `leave_type_name`,`lt`.`is_lop` AS `is_lop`,`smlb`.`year` AS `year`,sum(`smlb`.`earned_in_month`) AS `total_earned`,sum(`smlb`.`used_for_lop_adjustment`) AS `total_used_for_lop`,sum(`smlb`.`used_for_leave_application`) AS `total_used_for_leave`,sum(`smlb`.`other_deductions`) AS `total_other_deductions`,max(`smlb`.`closing_balance`) AS `current_balance` from ((`staff_monthly_leave_balance` `smlb` join `staff` `s` on(`smlb`.`staff_id` = `s`.`id`)) join `leave_types` `lt` on(`smlb`.`leave_type_id` = `lt`.`id`)) where `smlb`.`year` = year(curdate()) group by `smlb`.`staff_id`,`s`.`name`,`s`.`employee_id`,`smlb`.`leave_type_id`,`lt`.`type`,`lt`.`is_lop`,`smlb`.`year` */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;

-- Seed data for fresh install
SET FOREIGN_KEY_CHECKS=0;

--
-- Dumping data for table `sessions`
--

LOCK TABLES `sessions` WRITE;
/*!40000 ALTER TABLE `sessions` DISABLE KEYS */;
INSERT INTO `sessions` VALUES (1,'2025-26','yes','2025-04-01 00:00:00','2025-04-01 00:00:00');
/*!40000 ALTER TABLE `sessions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `sch_settings` (generic)
--

LOCK TABLES `sch_settings` WRITE;
/*!40000 ALTER TABLE `sch_settings` DISABLE KEYS */;
INSERT INTO `sch_settings` VALUES (1,'college','','','Your Institution',0,1,0,'','admin@example.com','','',4,'["4"]','AMACE','d-m-Y','12-hour',68,'$','disabled','0,1',1,1,'Asia/Kolkata',1,'eb20349e363e11f1812e5e3e258c24b2eb2034a8363e11f1812e5e3e258c24b2','after_number','#,###.##','yes',7,1,'0.00','','','','','','blue.jpg',60,0,0,'','',0,1,0,0,NULL,NULL,NULL,'','',0,1,'no',1,'yes',1000,'','','percentage','2.00','',NULL,0,1,1,1,1,1,1,1,1,1,1,0,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,'mceadmin@beebasoft.com','http://localhost/amace/api/','','','','','',1,1,'Monday',0,'enabled','disabled',1,'enabled',1,0,'[\"email\"]','[\"email\"]',1,1,0,0,0,0,'','qrcode',1,0,0,0,'',NULL,NULL,0,'',NULL,NULL,0,'',NULL,NULL,NULL,'2022-12-30 01:14:20','2026-04-12 08:33:33',NULL,'16:30:00','13:00:00','16:30:00',2,2,'yearly',NULL,NULL,1,0,0,1,'1775982280-172800838069db56c84f8a1-matt01.png','1775982310-149450048969db56e6f2ef1-COLLEGE LOGO.jpg','https://amace.edu.in',0,3,2,1,NULL,'1.00',1,2,NULL,10,'compensation,comp-off,compoff,compensatory off','1,7',1,'','',NULL,1,NULL,1,1,NULL,1,3,5,15,30,0,'','','','',NULL,'');
/*!40000 ALTER TABLE `sch_settings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `addons`
--

LOCK TABLES `addons` WRITE;
/*!40000 ALTER TABLE `addons` DISABLE KEYS */;
INSERT INTO `addons` VALUES (1,47443722,'uploads/addon_images/sscbse_images.jpg','Smart School CBSE Examination','cbse-config','sscbse','cbse_examination','CBSE Examination addon adds CBSE Examination module in Smart School. Using this module teacher/staff can create and print marksheets with advance features.',0.00,NULL,'https://go.smart-school.in/cbse-exam',NULL,NULL,NULL,NULL,NULL,'2024-09-03 16:04:58',0,4,'2024-09-03 16:04:58','2025-03-03 05:32:37'),(2,44278049,'uploads/addon_images/sstfa_images.jpg','Smart School Two Factor Authentication','google-authenticate-config','sstfa','two_factor_authentication','Two Factor Authentication addon adds Two Factor Authentication module in Smart School. Using this module you can enhance login security of your Smart School users.',0.00,NULL,'https://go.smart-school.in/2fa',NULL,NULL,NULL,NULL,NULL,'2025-01-29 11:16:14',0,5,'2024-09-07 10:45:18','2025-03-03 05:32:28'),(3,44277916,'uploads/addon_images/ssmb_images.jpg','Smart School Multi Branch','multibranch-config','ssmb','multi_branch','Multi Branch addon adds Multi Branch module in Smart School. Using this module Superadmin user can add other any number of schools/branches.',0.00,NULL,'https://go.smart-school.in/multi-branch',NULL,NULL,NULL,NULL,NULL,'2025-11-08 06:43:21',0,6,'2024-09-07 10:45:18','2025-11-08 01:13:21'),(4,44247532,'uploads/addon_images/ssbr_images.jpg','Smart School Behaviour Records','behaviour-report-config','ssbr','behavior_records','Behaviour Records addon adds Behaviour Records module in Smart School. Using this module teacher/staff can create different incidents with positive/negative marks and then assign these incidents on students.',0.00,NULL,'https://go.smart-school.in/behaviour-records',NULL,NULL,NULL,NULL,NULL,'2025-01-29 11:16:19',0,7,'2024-09-07 10:45:42','2025-03-03 05:32:11'),(5,33101540,'uploads/addon_images/ssoclc_images.jpg','Smart School Online Course','onlinecourse-config','ssoclc','online_course','Online Course addon adds Online Course module in Smart School. Using this module teacher/staff can create free or paid online course with their study material based on video, audio or in document content format.',0.00,NULL,'https://go.smart-school.in/online-course',NULL,NULL,NULL,NULL,NULL,'2025-01-29 11:16:19',0,8,'2024-09-07 10:45:42','2025-03-03 05:32:02'),(6,27492043,'uploads/addon_images/sszlc_images.jpg','Smart School Zoom Live Classes','zoom-config','sszlc','zoom_live_class','Zoom Live Class addon adds Zoom Live Class module in Smart School. Using this module teacher/staff can create live online classes using Zoom.us service. Further students can join these classes.',0.00,NULL,'https://go.smart-school.in/zoom',NULL,NULL,NULL,NULL,NULL,'2025-01-29 11:16:17',0,10,'2024-09-07 10:46:10','2025-03-03 05:31:49'),(7,28941973,'uploads/addon_images/ssglc_images.jpg','Smart School Gmeet Live Class','gmeet-config','ssglc','gmeet_live_class','Gmeet Live Class addon adds Gmeet Live Class module in Smart School. Using this module teacher/staff can create live online classes using http://meet.google.com service. Further students can join these classes.',0.00,NULL,'https://go.smart-school.in/gmeet',NULL,NULL,NULL,NULL,NULL,'2025-01-29 11:16:24',0,9,'2024-09-07 10:46:10','2025-03-03 05:31:33'),(8,50336584,'uploads/addon_images/ssqra_images.jpg','Smart School QR Code Attendance','qrattendance-config','ssqra','qr_code_attendance','QR Code Attendance addon adds automated Student/Staff attendance using QR/Barcode module in Smart School. Using this module Student/Staff can submit their attendance by just scanning their ID Card.',0.00,NULL,'https://go.smart-school.in/qr-attendance',NULL,NULL,NULL,NULL,NULL,'2025-01-28 22:28:58',0,3,'2025-01-13 13:10:06','2025-03-03 05:32:47'),(9,57220011,'uploads/addon_images/ssqfc_images.jpg','Smart School Quick Fees Create','quickfees-config','ssqfc','quick_fees_create','Quick Fees Create addon adds one click fees create feature in Smart School Fees Collection module. Using this you can create and assign fees on students in just few seconds and all Fees Category, Fees Groups, Fees Masters will be create automatically in your Smart School.',0.00,NULL,'https://go.smart-school.in/quick-fees',NULL,NULL,NULL,NULL,NULL,'2025-01-28 22:28:58',0,2,'2025-01-13 13:10:06','2025-03-03 05:33:00'),(10,57219905,'uploads/addon_images/sstpa_images.jpg','Smart School Thermal Print','thermalprint-config','sstpa','thermal_print','Thermal Print addon adds Thermal Printer compatible small size fees receipt print capability in Smart School. Using this module you can utilize modern cost effective fees receipt printing in Smart School.',0.00,NULL,'https://go.smart-school.in/thermal-print',NULL,NULL,NULL,NULL,NULL,'2025-01-30 10:28:42',0,1,'2025-01-13 13:10:06','2025-03-03 05:33:06');
/*!40000 ALTER TABLE `addons` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `attendence_type`
--

LOCK TABLES `attendence_type` WRITE;
/*!40000 ALTER TABLE `attendence_type` DISABLE KEYS */;
INSERT INTO `attendence_type` VALUES (1,'Present','<b class=\"text text-success\">P</b>','present','label label-success','yes',1,1,'2023-12-13 07:53:10','0000-00-00 00:00:00'),(2,'Late With Excuse','<b class=\"text text-warning\">E</b>','late_with_excuse','label label-warning text-dark','no',0,0,'2023-12-13 07:51:03','0000-00-00 00:00:00'),(3,'Late','<b class=\"text text-warning\">L</b>','late','label label-warning text-dark','yes',1,1,'2023-12-13 07:51:09','0000-00-00 00:00:00'),(4,'Absent','<b class=\"text text-danger\">A</b>','absent','label label-danger','yes',0,0,'2023-12-15 06:18:05','0000-00-00 00:00:00'),(5,'Holiday','H','holiday','label label-info','yes',0,0,'2023-12-14 12:57:13','0000-00-00 00:00:00'),(6,'Half Day','<b class=\"text text-warning\">F</b>','half_day','label label-warning text-dark','yes',1,1,'2023-12-15 06:18:37','0000-00-00 00:00:00');
/*!40000 ALTER TABLE `attendence_type` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `currencies`
--

LOCK TABLES `currencies` WRITE;
/*!40000 ALTER TABLE `currencies` DISABLE KEYS */;
INSERT INTO `currencies` VALUES (1,'AED','AED','AEDf','1',0,'2022-12-30 06:19:15','2025-09-25 15:40:50'),(2,'AFN','AFN','؋','1',0,'2022-12-30 06:19:19','2025-09-25 15:40:50'),(3,'ALL','ALL','ALL','1',0,'2022-12-30 06:19:22','2025-09-25 15:40:50'),(4,'AMD','AMD','AMD','1',0,'2022-07-22 10:55:15','2025-09-25 15:40:50'),(5,'ANG','ANG','ANG','1',0,'2022-07-22 10:55:15','2025-09-25 15:40:50'),(6,'AOA','AOA','AOA','1',0,'2022-07-22 10:55:15','2025-09-25 15:40:50'),(7,'ARS','ARS','ARS','1',0,'2022-07-22 10:55:15','2025-09-25 15:40:50'),(8,'AUD','AUD','AUD','1',0,'2022-07-22 10:55:15','2025-09-25 15:40:50'),(9,'AWG','AWG','AWG','1',0,'2022-07-22 10:55:15','2025-09-25 15:40:50'),(10,'AZN','AZN','AZN','1',0,'2022-07-22 10:55:15','2025-09-25 15:40:50'),(11,'BAM','BAM','BAM','1',0,'2022-07-22 10:55:15','2025-09-25 15:40:50'),(12,'BAM','BAM','BAM','1',0,'2022-07-22 10:55:15','2025-09-25 15:40:50'),(13,'BDT','BDT','BDT','1',0,'2022-07-22 10:55:15','2025-09-25 15:40:50'),(14,'BGN','BGN','BGN','1',0,'2022-07-22 10:55:15','2025-09-25 15:40:50'),(15,'BHD','BHD','BHD','1',0,'2022-07-22 10:55:15','2025-09-25 15:40:50'),(16,'BIF','BIF','BIF','1',0,'2022-07-22 10:55:15','2025-09-25 15:40:50'),(17,'BMD','BMD','BMD','1',0,'2022-07-22 10:55:15','2025-09-25 15:40:50'),(18,'BND','BND','BND','1',0,'2022-07-22 10:55:15','2025-09-25 15:40:50'),(19,'BOB','BOB','BOB','1',0,'2022-12-30 06:19:29','2025-09-25 15:40:50'),(20,'BOV','BOV','BOV','1',0,'2022-12-30 06:19:38','2025-09-25 15:40:50'),(21,'BRL','BRL','BRL','1',0,'2022-07-22 10:55:15','2025-09-25 15:40:50'),(22,'BSD','BSD','BSD','1',0,'2022-12-30 06:19:40','2025-09-25 15:40:50'),(23,'BTN','BTN','BTN','1',0,'2022-12-30 06:19:42','2025-09-25 15:40:50'),(24,'BWP','BWP','BWP','1',0,'2022-07-22 10:55:15','2025-09-25 15:40:50'),(25,'BYN','BYN','BYN','1',0,'2022-07-22 10:55:15','2025-09-25 15:40:50'),(26,'BYR','BYR','BYR','1',0,'2022-07-22 10:55:15','2025-09-25 15:40:50'),(27,'BZD','BZD','BZD','1',0,'2022-07-22 10:55:15','2025-09-25 15:40:50'),(28,'CAD','CAD','CAD','1',0,'2022-07-22 10:55:15','2025-09-25 15:40:50'),(29,'CDF','CDF','CDF','1',0,'2022-07-22 10:55:15','2025-09-25 15:40:50'),(30,'CHE','CHE','CHE','1',0,'2022-07-22 10:55:15','2025-09-25 15:40:50'),(31,'CHF','CHF','CHF','1',0,'2022-07-22 10:55:15','2025-09-25 15:40:50'),(32,'CHW','CHW','CHW','1',0,'2022-07-22 10:55:15','2025-09-25 15:40:50'),(33,'CLF','CLF','CLF','1',0,'2022-07-22 10:55:15','2025-09-25 15:40:50'),(34,'CLP','CLP','CLP','1',0,'2022-07-22 10:55:15','2025-09-25 15:40:50'),(35,'CNY','CNY','CNY','1',0,'2022-07-22 10:55:15','2025-09-25 15:40:50'),(36,'COP','COP','COP','1',0,'2022-07-22 10:55:15','2025-09-25 15:40:50'),(37,'COU','COU','COU','1',0,'2022-07-22 10:55:15','2025-09-25 15:40:50'),(38,'CRC','CRC','CRC','1',0,'2022-07-22 10:55:15','2025-09-25 15:40:50'),(39,'CUC','CUC','CUC','1',0,'2022-07-22 10:55:15','2025-09-25 15:40:50'),(40,'CUP','CUP','CUP','1',0,'2022-07-22 10:55:15','2025-09-25 15:40:50'),(41,'CVE','CVE','CVE','1',0,'2022-07-22 10:55:15','2025-09-25 15:40:50'),(42,'CZK','CZK','CZK','1',0,'2022-07-22 10:55:15','2025-09-25 15:40:50'),(43,'DJF','DJF','DJF','1',0,'2022-07-22 10:55:15','2025-09-25 15:40:50'),(44,'DKK','DKK','DKK','1',0,'2022-07-22 10:55:15','2025-09-25 15:40:50'),(45,'DOP','DOP','DOP','1',0,'2022-07-22 10:55:15','2025-09-25 15:40:50'),(46,'DZD','DZD','DZD','1',0,'2022-07-22 10:55:15','2025-09-25 15:40:50'),(47,'EGP','EGP','EGP','1',0,'2022-07-22 10:55:15','2025-09-25 15:40:50'),(48,'ERN','ERN','ERN','1',0,'2022-07-22 10:55:15','2025-09-25 15:40:50'),(49,'ETB','ETB','ETB','1',0,'2022-07-22 10:55:15','2025-09-25 15:40:50'),(50,'EUR','EUR','€','1',0,'2022-12-30 06:20:25','2025-09-25 15:40:50'),(51,'FJD','FJD','FJD','1',0,'2022-07-22 10:55:15','2025-09-25 15:40:50'),(52,'FKP','FKP','FKP','1',0,'2022-07-22 10:55:15','2025-09-25 15:40:50'),(53,'GBP','GBP','£','1',0,'2022-12-30 06:20:29','2025-09-25 15:40:50'),(54,'GEL','GEL','GEL','1',0,'2022-07-22 10:55:15','2025-09-25 15:40:50'),(55,'GHS','GHS','GHS','1',0,'2022-07-22 10:55:15','2025-09-25 15:40:50'),(56,'GIP','GIP','GIP','1',0,'2022-07-22 10:55:15','2025-09-25 15:40:50'),(57,'GMD','GMD','GMD','1',0,'2022-07-22 10:55:15','2025-09-25 15:40:50'),(58,'GNF','GNF','GNF','1',0,'2022-07-22 10:55:15','2025-09-25 15:40:50'),(59,'GTQ','GTQ','GTQ','1',0,'2022-07-22 10:55:15','2025-09-25 15:40:50'),(60,'GYD','GYD','GYD','1',0,'2022-07-22 10:55:15','2025-09-25 15:40:50'),(61,'HKD','HKD','HKD','1',0,'2022-07-22 10:55:15','2025-09-25 15:40:50'),(62,'HNL','HNL','HNL','1',0,'2022-07-22 10:55:15','2025-09-25 15:40:50'),(63,'HRK','HRK','HRK','1',0,'2022-07-22 10:55:15','2025-09-25 15:40:50'),(64,'HTG','HTG','HTG','1',0,'2022-07-22 10:55:15','2025-09-25 15:40:50'),(65,'HUF','HUF','HUF','1',0,'2022-07-22 10:55:15','2025-09-25 15:40:50'),(66,'IDR','IDR','IDR','1',0,'2022-12-30 06:20:34','2025-09-25 15:40:50'),(67,'ILS','ILS','ILS','1',0,'2022-07-22 10:55:15','2025-09-25 15:40:50'),(68,'INR','INR','₹','1',1,'2022-12-30 06:20:37','2025-10-02 13:18:13'),(69,'IQD','IQD','IQD','1',0,'2022-07-22 10:55:15','2025-09-25 15:40:50'),(70,'IRR','IRR','IRR','1',0,'2022-07-22 10:55:15','2025-09-25 15:40:50'),(71,'ISK','ISK','ISK','1',0,'2022-07-22 10:55:15','2025-09-25 15:40:50'),(72,'JMD','JMD','JMD','1',0,'2022-07-22 10:55:15','2025-09-25 15:40:50'),(73,'JOD','JOD','JOD','1',0,'2022-07-22 10:55:15','2025-09-25 15:40:50'),(74,'JPY','JPY','JPY','1',0,'2022-12-30 06:19:56','2025-09-25 15:40:50'),(75,'KES','KES','KES','1',0,'2022-07-22 10:55:15','2025-09-25 15:40:50'),(76,'KGS','KGS','KGS','1',0,'2022-07-22 10:55:15','2025-09-25 15:40:50'),(77,'KHR','KHR','KHR','1',0,'2022-07-22 10:55:15','2025-09-25 15:40:50'),(78,'KMF','KMF','KMF','1',0,'2022-07-22 10:55:15','2025-09-25 15:40:50'),(79,'KPW','KPW','KPW','1',0,'2022-07-22 10:55:15','2025-09-25 15:40:50'),(80,'KRW','KRW','KRW','1',0,'2022-07-22 10:55:15','2025-09-25 15:40:50'),(81,'KWD','KWD','KWD','1',0,'2022-07-22 10:55:15','2025-09-25 15:40:50'),(82,'KYD','KYD','KYD','1',0,'2022-07-22 10:55:15','2025-09-25 15:40:50'),(83,'KZT','KZT','KZT','1',0,'2022-07-22 10:55:15','2025-09-25 15:40:50'),(84,'LAK','LAK','LAK','1',0,'2022-07-22 10:55:15','2025-09-25 15:40:50'),(85,'LBP','LBP','LBP','1',0,'2022-07-22 10:55:15','2025-09-25 15:40:50'),(86,'LKR','LKR','LKR','1',0,'2022-07-22 10:55:15','2025-09-25 15:40:50'),(87,'LRD','LRD','LRD','1',0,'2022-07-22 10:55:15','2025-09-25 15:40:50'),(88,'LSL','LSL','LSL','1',0,'2022-07-22 10:55:15','2025-09-25 15:40:50'),(89,'LYD','LYD','LYD','1',0,'2022-07-22 10:55:15','2025-09-25 15:40:50'),(90,'MAD','MAD','MAD','1',0,'2022-07-22 10:55:15','2025-09-25 15:40:50'),(91,'MDL','MDL','MDL','1',0,'2022-07-22 10:55:15','2025-09-25 15:40:50'),(92,'MGA','MGA','MGA','1',0,'2022-07-22 10:55:15','2025-09-25 15:40:50'),(93,'MKD','MKD','MKD','1',0,'2022-07-22 10:55:15','2025-09-25 15:40:50'),(94,'MMK','MMK','MMK','1',0,'2022-07-22 10:55:15','2025-09-25 15:40:50'),(95,'MNT','MNT','MNT','1',0,'2022-07-22 10:55:15','2025-09-25 15:40:50'),(96,'MOP','MOP','MOP','1',0,'2022-07-22 10:55:15','2025-09-25 15:40:50'),(97,'MRO','MRO','MRO','1',0,'2022-07-22 10:55:15','2025-09-25 15:40:50'),(98,'MUR','MUR','MUR','1',0,'2022-07-22 10:55:15','2025-09-25 15:40:50'),(99,'MVR','MVR','MVR','1',0,'2022-07-22 10:55:15','2025-09-25 15:40:50'),(100,'MWK','MWK','MWK','1',0,'2022-07-22 10:55:15','2025-09-25 15:40:50'),(101,'MXN','MXN','MXN','1',0,'2022-07-22 10:55:15','2025-09-25 15:40:50'),(102,'MXV','MXV','MXV','1',0,'2022-07-22 10:55:15','2025-09-25 15:40:50'),(103,'MYR','MYR','MYR','1',0,'2022-07-22 10:55:15','2025-09-25 15:40:50'),(104,'MZN','MZN','MZN','1',0,'2022-07-22 10:55:15','2025-09-25 15:40:50'),(105,'NAD','NAD','NAD','1',0,'2022-07-30 09:32:37','2025-09-25 15:40:50'),(106,'NGN','NGN','NGN','1',0,'2022-12-30 06:20:42','2025-09-25 15:40:50'),(107,'NIO','NIO','NIO','1',0,'2022-07-22 10:55:15','2025-09-25 15:40:50'),(108,'NOK','NOK','NOK','1',0,'2022-07-22 10:55:15','2025-09-25 15:40:50'),(109,'NPR','NPR','NPR','1',0,'2022-07-22 10:55:15','2025-09-25 15:40:50'),(110,'NZD','NZD','NZD','1',0,'2022-07-22 10:55:15','2025-09-25 15:40:50'),(111,'OMR','OMR','OMR','1',0,'2022-07-22 10:55:15','2025-09-25 15:40:50'),(112,'PAB','PAB','PAB','1',0,'2022-07-22 10:55:15','2025-09-25 15:40:50'),(113,'PEN','PEN','PEN','1',0,'2022-07-22 10:55:15','2025-09-25 15:40:50'),(114,'PGK','PGK','PGK','1',0,'2022-07-22 10:55:15','2025-09-25 15:40:50'),(115,'PHP','PHP','PHP','1',0,'2022-07-22 10:55:15','2025-09-25 15:40:50'),(116,'PKR','PKR','PKR','1',0,'2022-12-30 06:20:19','2025-09-25 15:40:50'),(117,'PLN','PLN','PLN','1',0,'2022-07-22 10:55:15','2025-09-25 15:40:50'),(118,'PYG','PYG','PYG','1',0,'2022-07-22 10:55:15','2025-09-25 15:40:50'),(119,'QAR','QAR','QAR','1',0,'2022-07-22 10:55:15','2025-09-25 15:40:50'),(120,'RON','RON','RON','1',0,'2022-07-22 10:55:15','2025-09-25 15:40:50'),(121,'RSD','RSD','RSD','1',0,'2022-07-22 10:55:15','2025-09-25 15:40:50'),(122,'RUB','RUB','RUB','1',0,'2022-12-30 06:20:16','2025-09-25 15:40:50'),(123,'RWF','RWF','RWF','1',0,'2022-07-22 10:55:15','2025-09-25 15:40:50'),(124,'SAR','SAR','SAR','1',0,'2022-07-22 10:55:15','2025-09-25 15:40:50'),(125,'SBD','SBD','SBD','1',0,'2022-07-22 10:55:15','2025-09-25 15:40:50'),(126,'SCR','SCR','SCR','1',0,'2022-07-22 10:55:15','2025-09-25 15:40:50'),(127,'SDG','SDG','SDG','1',0,'2022-07-22 10:55:15','2025-09-25 15:40:50'),(128,'SEK','SEK','SEK','1',0,'2022-07-22 10:55:15','2025-09-25 15:40:50'),(129,'SGD','SGD','SGD','1',0,'2022-07-22 10:55:15','2025-09-25 15:40:50'),(130,'SHP','SHP','SHP','1',0,'2022-07-22 10:55:15','2025-09-25 15:40:50'),(131,'SLL','SLL','SLL','1',0,'2022-07-22 10:55:15','2025-09-25 15:40:50'),(132,'SOS','SOS','SOS','1',0,'2022-07-22 10:55:15','2025-09-25 15:40:50'),(133,'SRD','SRD','SRD','1',0,'2022-07-22 10:55:15','2025-09-25 15:40:50'),(134,'SSP','SSP','SSP','1',0,'2022-07-22 10:55:15','2025-09-25 15:40:50'),(135,'STD','STD','STD','1',0,'2022-07-22 10:55:15','2025-09-25 15:40:50'),(136,'SVC','SVC','SVC','1',0,'2022-07-22 10:55:15','2025-09-25 15:40:50'),(137,'SYP','SYP','SYP','1',0,'2022-07-22 10:55:15','2025-09-25 15:40:50'),(138,'SZL','SZL','SZL','1',0,'2022-07-22 10:55:15','2025-09-25 15:40:50'),(139,'THB','THB','THB','1',0,'2022-07-22 10:55:15','2025-09-25 15:40:50'),(140,'TJS','TJS','TJS','1',0,'2022-07-22 10:55:15','2025-09-25 15:40:50'),(141,'TMT','TMT','TMT','1',0,'2022-07-22 10:55:15','2025-09-25 15:40:50'),(142,'TND','TND','TND','1',0,'2022-07-22 10:55:15','2025-09-25 15:40:50'),(143,'TOP','TOP','TOP','1',0,'2022-07-22 10:55:15','2025-09-25 15:40:50'),(144,'TRY','TRY','TRY','1',0,'2022-07-22 10:55:15','2025-09-25 15:40:50'),(145,'TTD','TTD','TTD','1',0,'2022-07-22 10:55:15','2025-09-25 15:40:50'),(146,'TWD','TWD','TWD','1',0,'2022-07-22 10:55:15','2025-09-25 15:40:50'),(147,'TZS','TZS','TZS','1',0,'2022-07-22 10:55:15','2025-09-25 15:40:50'),(148,'UAH','UAH','UAH','1',0,'2022-07-22 10:55:15','2025-09-25 15:40:50'),(149,'UGX','UGX','UGX','1',0,'2022-07-22 10:55:15','2025-09-25 15:40:50'),(150,'USD','USD','$','1',1,'2022-07-22 10:55:15','2025-09-25 15:40:50'),(151,'USN','USN','USN','1',0,'2022-12-30 06:20:03','2025-09-25 15:40:50'),(152,'UYI','UYI','UYI','1',0,'2022-07-22 10:55:15','2025-09-25 15:40:50'),(153,'UYU','UYU','UYU','1',0,'2022-07-22 10:55:15','2025-09-25 15:40:50'),(154,'UZS','UZS','UZS','1',0,'2022-07-22 10:55:15','2025-09-25 15:40:50'),(155,'VEF','VEF','VEF','1',0,'2022-07-22 10:55:15','2025-09-25 15:40:50'),(156,'VND','VND','VND','1',0,'2022-07-22 10:55:15','2025-09-25 15:40:50'),(157,'VUV','VUV','VUV','1',0,'2022-07-22 10:55:15','2025-09-25 15:40:50'),(158,'WST','WST','WST','1',0,'2022-07-22 10:55:15','2025-09-25 15:40:50'),(159,'XAF','XAF','XAF','1',0,'2022-07-22 10:55:15','2025-09-25 15:40:50'),(160,'XAG','XAG','XAG','1',0,'2022-07-22 10:55:15','2025-09-25 15:40:50'),(161,'XAU','XAU','XAU','1',0,'2022-07-22 10:55:15','2025-09-25 15:40:50'),(162,'XBA','XBA','XBA','1',0,'2022-07-22 10:55:15','2025-09-25 15:40:50'),(163,'XBB','XBB','XBB','1',0,'2022-07-22 10:55:15','2025-09-25 15:40:50'),(164,'XBC','XBC','XBC','1',0,'2022-07-22 10:55:15','2025-09-25 15:40:50'),(165,'XBD','XBD','XBD','1',0,'2022-07-22 10:55:15','2025-09-25 15:40:50'),(166,'XCD','XCD','XCD','1',0,'2022-07-22 10:55:15','2025-09-25 15:40:50'),(167,'XDR','XDR','XDR','1',0,'2022-07-22 10:55:15','2025-09-25 15:40:50'),(168,'XOF','XOF','XOF','1',0,'2022-07-22 10:55:15','2025-09-25 15:40:50'),(169,'XPD','XPD','XPD','1',0,'2022-07-22 10:55:15','2025-09-25 15:40:50'),(170,'XPF','XPF','XPF','1',0,'2022-07-22 10:55:15','2025-09-25 15:40:50'),(171,'XPT','XPT','XPT','1',0,'2022-07-22 10:55:15','2025-09-25 15:40:50'),(172,'XSU','XSU','XSU','1',0,'2022-07-22 10:55:15','2025-09-25 15:40:50'),(173,'XTS','XTS','XTS','1',0,'2022-07-22 10:55:15','2025-09-25 15:40:50'),(174,'XUA','XUA','XUA','1',0,'2022-07-22 10:55:15','2025-09-25 15:40:50'),(175,'XXX','XXX','XXX','1',0,'2022-07-22 10:55:15','2025-09-25 15:40:50'),(176,'YER','YER','YER','1',0,'2022-07-22 10:55:15','2025-09-25 15:40:50'),(177,'ZAR','ZAR','ZAR','1',0,'2022-12-30 06:20:07','2025-09-25 15:40:50'),(178,'ZMW','ZMW','ZMW','1',0,'2022-07-30 07:34:00','2025-09-25 15:40:50'),(179,'ZWL','ZWL','ZWL','1',0,'2022-07-22 10:55:15','2025-09-25 15:40:50');
/*!40000 ALTER TABLE `currencies` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `disable_reason`
--

LOCK TABLES `disable_reason` WRITE;
/*!40000 ALTER TABLE `disable_reason` DISABLE KEYS */;
INSERT INTO `disable_reason` VALUES (1,'admission not confirmed yet','2025-10-07 08:39:30','2025-10-07 08:39:30'),(2,'Tech','2025-10-07 08:39:50','2025-10-07 08:39:50'),(3,'Long Leave','2025-11-21 07:54:00','2025-11-21 07:54:00'),(4,'Discontinued','2025-11-21 07:58:28','2025-11-21 07:58:28');
/*!40000 ALTER TABLE `disable_reason` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `filetypes`
--

LOCK TABLES `filetypes` WRITE;
/*!40000 ALTER TABLE `filetypes` DISABLE KEYS */;
INSERT INTO `filetypes` VALUES (1,'pdf, zip, jpg, jpeg, png, txt, 7z, gif, csv, docx, mp3, mp4, accdb, odt, ods, ppt, pptx, xlsx, wmv, jfif, apk, ppt, bmp, jpe, mdb, rar, xls, svg','application/pdf, image/zip, image/jpg, image/png, image/jpeg, text/plain, application/x-zip-compressed, application/zip, image/gif, text/csv, application/vnd.openxmlformats-officedocument.wordprocessingml.document, audio/mpeg, application/msaccess, application/vnd.oasis.opendocument.text, application/vnd.oasis.opendocument.spreadsheet, application/vnd.ms-powerpoint, application/vnd.openxmlformats-officedocument.presentationml.presentation, application/vnd.openxmlformats-officedocument.spreadsheetml.sheet, video/x-ms-wmv, video/mp4, image/jpeg, application/vnd.android.package-archive, application/x-msdownload, application/vnd.ms-powerpoint, image/bmp, image/jpeg, application/msaccess, application/vnd.ms-excel, image/svg+xml',100048576,'jfif, png, jpe, jpeg, jpg, bmp, gif, svg','image/jpeg, image/png, image/jpeg, image/jpeg, image/bmp, image/gif, image/x-ms-bmp, image/svg+xml',10048576,'2021-01-30 13:03:03','2025-09-25 15:40:51');
/*!40000 ALTER TABLE `filetypes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `holiday_type`
--

LOCK TABLES `holiday_type` WRITE;
/*!40000 ALTER TABLE `holiday_type` DISABLE KEYS */;
INSERT INTO `holiday_type` VALUES (1,'Holiday',1),(2,'Vacation',1),(3,'Activity',1),(4,'Government Holiday',0),(5,'Natural Calamity Holiday',0),(6,'Compensation',1);
/*!40000 ALTER TABLE `holiday_type` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `incidental_fee_types`
--

LOCK TABLES `incidental_fee_types` WRITE;
/*!40000 ALTER TABLE `incidental_fee_types` DISABLE KEYS */;
INSERT INTO `incidental_fee_types` VALUES (1,'Fine Amount','to be collected as fine',NULL,0,'2025-11-03 09:26:38','2025-11-03 09:26:38',1),(3,'LUNCH TOKEN','',50.00,1,'2026-02-03 10:24:47','2026-02-03 10:25:44',474),(4,'TUITION FEE (2026 - 27)','',NULL,0,'2026-02-04 05:24:01','2026-02-04 05:24:01',474),(5,'OTHER FEE (2026 - 27)','',NULL,0,'2026-02-04 05:24:23','2026-02-04 05:24:23',474),(6,'ALUMINI FEE','',NULL,0,'2026-02-04 06:13:38','2026-02-04 06:13:38',474),(7,'WORKSHOP AMOUNT','',NULL,0,'2026-02-04 10:12:05','2026-02-04 10:12:05',474),(8,'TRANSPORT FEE TEMPORARY','',NULL,0,'2026-02-05 05:57:29','2026-02-13 08:23:55',474),(9,'PASSEDOUT FEE','',NULL,0,'2026-02-06 05:34:45','2026-02-06 05:34:45',474),(10,'APPLICATION FEE (2026-27)','',NULL,0,'2026-02-06 08:07:10','2026-02-06 08:07:10',474),(11,'1 - YEAR BOOK FEES SEM- II','',NULL,0,'2026-02-20 04:48:07','2026-02-23 05:26:50',474),(12,'SYMPOSIUM','',NULL,0,'2026-03-04 10:05:59','2026-03-04 10:05:59',474),(13,'PHOTO COPY','',NULL,0,'2026-03-16 06:58:25','2026-03-16 06:58:25',474),(16,'EXAM FEE','',NULL,1,'2026-03-17 05:46:39','2026-03-17 07:18:02',474),(17,'SPORTS, LIBRARY FEE','',NULL,0,'2026-03-23 09:53:03','2026-03-23 09:53:03',474),(18,'ARREAR FEE','',NULL,0,'2026-04-08 07:31:19','2026-04-08 07:31:19',474);
/*!40000 ALTER TABLE `incidental_fee_types` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `languages`
--

LOCK TABLES `languages` WRITE;
/*!40000 ALTER TABLE `languages` DISABLE KEYS */;
INSERT INTO `languages` VALUES (1,'Azerbaijan','az','az',0,'no','no','2019-11-20 11:23:12','0000-00-00 00:00:00'),(2,'Albanian','sq','al',0,'no','no','2019-11-20 11:42:42','0000-00-00 00:00:00'),(3,'Amharic','am','am',0,'no','no','2019-11-20 11:24:23','0000-00-00 00:00:00'),(4,'English','en','us',0,'no','no','2019-11-20 11:38:50','0000-00-00 00:00:00'),(5,'Arabic','ar','sa',0,'no','no','2019-11-20 11:47:28','0000-00-00 00:00:00'),(7,'Afrikaans','af','af',0,'no','no','2019-11-20 11:24:23','0000-00-00 00:00:00'),(8,'Basque','eu','es',0,'no','no','2019-11-20 11:54:10','0000-00-00 00:00:00'),(11,'Bengali','bn','in',0,'no','no','2019-11-20 11:41:53','0000-00-00 00:00:00'),(13,'Bosnian','bs','bs',0,'no','no','2019-11-20 11:24:23','0000-00-00 00:00:00'),(14,'Welsh','cy','cy',0,'no','no','2019-11-20 11:24:23','0000-00-00 00:00:00'),(15,'Hungarian','hu','hu',0,'no','no','2019-11-20 11:24:23','0000-00-00 00:00:00'),(16,'Vietnamese','vi','vi',0,'no','no','2019-11-20 11:24:23','0000-00-00 00:00:00'),(17,'Haitian','ht','ht',0,'no','no','2021-01-23 07:09:32','0000-00-00 00:00:00'),(18,'Galician','gl','gl',0,'no','no','2019-11-20 11:24:23','0000-00-00 00:00:00'),(19,'Dutch','nl','nl',0,'no','no','2019-11-20 11:24:23','0000-00-00 00:00:00'),(21,'Greek','el','gr',0,'no','no','2019-11-20 12:12:08','0000-00-00 00:00:00'),(22,'Georgian','ka','ge',0,'no','no','2019-11-20 12:11:40','0000-00-00 00:00:00'),(23,'Gujarati','gu','in',0,'no','no','2019-11-20 11:39:16','0000-00-00 00:00:00'),(24,'Danish','da','dk',0,'no','no','2019-11-20 12:03:25','0000-00-00 00:00:00'),(25,'Hebrew','he','il',0,'no','no','2019-11-20 12:13:50','0000-00-00 00:00:00'),(26,'Yiddish','yi','il',0,'no','no','2019-11-20 12:25:33','0000-00-00 00:00:00'),(27,'Indonesian','id','id',0,'no','no','2019-11-20 11:24:23','0000-00-00 00:00:00'),(28,'Irish','ga','ga',0,'no','no','2019-11-20 11:24:23','0000-00-00 00:00:00'),(29,'Italian','it','it',0,'no','no','2019-11-20 11:24:23','0000-00-00 00:00:00'),(30,'Icelandic','is','is',0,'no','no','2019-11-20 11:24:23','0000-00-00 00:00:00'),(31,'Spanish','es','es',0,'no','no','2019-11-20 11:24:23','0000-00-00 00:00:00'),(33,'Kannada','kn','kn',0,'no','no','2019-11-20 11:24:23','0000-00-00 00:00:00'),(34,'Catalan','ca','ca',0,'no','no','2019-11-20 11:24:23','0000-00-00 00:00:00'),(36,'Chinese','zh','cn',0,'no','no','2019-11-20 12:01:48','0000-00-00 00:00:00'),(37,'Korean','ko','kr',0,'no','no','2019-11-20 12:19:09','0000-00-00 00:00:00'),(38,'Xhosa','xh','ls',0,'no','no','2019-11-20 12:24:39','0000-00-00 00:00:00'),(39,'Latin','la','it',0,'no','no','2021-01-23 07:09:32','0000-00-00 00:00:00'),(40,'Latvian','lv','lv',0,'no','no','2019-11-20 11:24:23','0000-00-00 00:00:00'),(41,'Lithuanian','lt','lt',0,'no','no','2019-11-20 11:24:23','0000-00-00 00:00:00'),(43,'Malagasy','mg','mg',0,'no','no','2019-11-20 11:24:23','0000-00-00 00:00:00'),(44,'Malay','ms','ms',0,'no','no','2019-11-20 11:24:23','0000-00-00 00:00:00'),(45,'Malayalam','ml','ml',0,'no','no','2019-11-20 11:24:23','0000-00-00 00:00:00'),(46,'Maltese','mt','mt',0,'no','no','2019-11-20 11:24:23','0000-00-00 00:00:00'),(47,'Macedonian','mk','mk',0,'no','no','2019-11-20 11:24:23','0000-00-00 00:00:00'),(48,'Maori','mi','nz',0,'no','no','2019-11-20 12:20:27','0000-00-00 00:00:00'),(49,'Marathi','mr','in',0,'no','no','2019-11-20 11:39:51','0000-00-00 00:00:00'),(51,'Mongolian','mn','mn',0,'no','no','2019-11-20 11:24:23','0000-00-00 00:00:00'),(52,'German','de','de',0,'no','no','2019-11-20 11:24:23','0000-00-00 00:00:00'),(53,'Nepali','ne','ne',0,'no','no','2019-11-20 11:24:23','0000-00-00 00:00:00'),(54,'Norwegian','no','no',0,'no','no','2019-11-20 11:24:23','0000-00-00 00:00:00'),(55,'Punjabi','pa','in',0,'no','no','2019-11-20 11:40:16','0000-00-00 00:00:00'),(57,'Persian','fa','ir',0,'no','no','2019-11-20 12:21:17','0000-00-00 00:00:00'),(59,'Portuguese','pt','pt',0,'no','no','2019-11-20 11:24:23','0000-00-00 00:00:00'),(60,'Romanian','ro','ro',0,'no','no','2019-11-20 11:24:23','0000-00-00 00:00:00'),(61,'Russian','ru','ru',0,'no','no','2019-11-20 11:24:23','0000-00-00 00:00:00'),(62,'Cebuano','ceb','ph',0,'no','no','2019-11-20 11:59:12','0000-00-00 00:00:00'),(64,'Sinhala','si','lk ',0,'no','no','2021-01-23 07:09:32','0000-00-00 00:00:00'),(65,'Slovakian','sk','sk',0,'no','no','2019-11-20 11:24:23','0000-00-00 00:00:00'),(66,'Slovenian','sl','sl',0,'no','no','2019-11-20 11:24:23','0000-00-00 00:00:00'),(67,'Swahili','sw','ke',0,'no','no','2019-11-20 12:21:57','0000-00-00 00:00:00'),(68,'Sundanese','su','sd',0,'no','no','2019-12-03 11:06:57','0000-00-00 00:00:00'),(70,'Thai','th','th',0,'no','no','2019-11-20 11:24:23','0000-00-00 00:00:00'),(71,'Tagalog','tl','tl',0,'no','no','2019-11-20 11:24:23','0000-00-00 00:00:00'),(72,'Tamil','ta','in',0,'no','no','2019-11-20 11:40:53','0000-00-00 00:00:00'),(74,'Telugu','te','in',0,'no','no','2019-11-20 11:41:15','0000-00-00 00:00:00'),(75,'Turkish','tr','tr',0,'no','no','2019-11-20 11:24:23','0000-00-00 00:00:00'),(77,'Uzbek','uz','uz',0,'no','no','2019-11-20 11:24:23','0000-00-00 00:00:00'),(79,'Urdu','ur','pk',0,'no','no','2019-11-20 12:23:57','0000-00-00 00:00:00'),(80,'Finnish','fi','fi',0,'no','no','2019-11-20 11:24:23','0000-00-00 00:00:00'),(81,'French','fr','fr',0,'no','no','2019-11-20 11:24:23','0000-00-00 00:00:00'),(82,'Hindi','hi','in',0,'no','no','2019-11-20 11:36:34','0000-00-00 00:00:00'),(84,'Czech','cs','cz',0,'no','no','2019-11-20 12:02:36','0000-00-00 00:00:00'),(85,'Swedish','sv','sv',0,'no','no','2019-11-20 11:24:23','0000-00-00 00:00:00'),(86,'Scottish','gd','gd',0,'no','no','2019-11-20 11:24:23','0000-00-00 00:00:00'),(87,'Estonian','et','et',0,'no','no','2019-11-20 11:24:23','0000-00-00 00:00:00'),(88,'Esperanto','eo','br',0,'no','no','2019-11-21 04:49:18','0000-00-00 00:00:00'),(89,'Javanese','jv','id',0,'no','no','2019-11-20 12:18:29','0000-00-00 00:00:00'),(90,'Japanese','ja','jp',0,'no','no','2019-11-20 12:14:39','0000-00-00 00:00:00'),(91,'Polish','pl','pl',0,'no','no','2020-06-15 03:25:27','0000-00-00 00:00:00'),(92,'Kurdish','ku','iq',0,'no','no','2020-12-21 00:15:31','0000-00-00 00:00:00'),(93,'Lao','lo','la',0,'no','no','2020-12-21 00:15:36','0000-00-00 00:00:00'),(94,'Croatia','hr','hr',0,'no','no','2022-06-07 11:48:21','0000-00-00 00:00:00');
/*!40000 ALTER TABLE `languages` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `leave_types`
--

LOCK TABLES `leave_types` WRITE;
/*!40000 ALTER TABLE `leave_types` DISABLE KEYS */;
INSERT INTO `leave_types` VALUES (1,'On Duty','yes','Staff',0,0,0,0,0,'All',0,NULL,1),(2,'Medical Leave','yes','All',0,1,1,0,0,'All',0,NULL,0),(3,'Casual Leave','yes','Staff',0,0,1,0,0,'All',0,NULL,0),(4,'CPL','yes','Staff',0,0,0,0,0,'All',0,5,1),(5,'HOD','yes','Staff',0,0,0,0,0,NULL,0,NULL,1);
/*!40000 ALTER TABLE `leave_types` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `notification_setting`
--

LOCK TABLES `notification_setting` WRITE;
/*!40000 ALTER TABLE `notification_setting` DISABLE KEYS */;
INSERT INTO `notification_setting` VALUES (1,'student_admission','1','0',0,0,1,1,1,0,1,1,NULL,'Student Admission','student_admission','Dear {{student_name}} your admission is confirm in Class: {{class}} Section: {{section}} for Session: {{current_session_name}} for more detail contact the Institution.','{{student_name}} {{class}}  {{section}}  {{admission_no}}  {{roll_no}}  {{admission_date}}   {{mobileno}}  {{email}}  {{dob}}  {{guardian_name}}  {{guardian_relation}}  {{guardian_phone}}  {{father_name}}  {{father_phone}}  {{blood_group}}  {{mother_name}}  {{gender}} {{guardian_email}} {{current_session_name}} ',NULL,'2022-12-28 09:52:24','2026-04-10 15:54:06',0,1),(2,'exam_result','1','0',0,1,1,1,1,0,1,1,NULL,'Exam Result','exam_result','Dear {{student_name}} - {{exam_roll_no}}, your {{exam}} result has been published.','{{student_name}} {{exam_roll_no}} {{exam}}',NULL,'2022-12-28 09:52:24','2026-02-24 08:31:42',0,1),(3,'fee_submission','1','0',0,1,1,1,1,0,1,1,NULL,'Fee Submission','fee_submission','Dear Parent, we are pleased to inform you that fees has been received for your ward {{student_name}} of Class {{class}} - Section {{section}}.\r\n\r\nPayment Summary:\r\nFee Category: {{fee_group_name}}\r\nInvoice No: {{invoice_id}}\r\nAmount Paid: Rs. {{fee_amount}}\r\nDue Date: {{due_date}}\r\n\r\nPlease keep this message as your digital acknowledgement of payment. For a detailed fee receipt, kindly visit the college accounts section or log in to the student portal.\r\n\r\nThank you for your prompt payment.','{{student_name}} {{class}} {{section}} {{fine_type}} {{fine_percentage}} {{fine_amount}} {{fee_group_name}} {{type}} {{code}} {{email}} {{contact_no}} {{invoice_id}} {{sub_invoice_id}} {{due_date}} {{amount}} {{fee_amount}}',NULL,'2022-12-28 09:52:24','2026-04-14 11:09:14',0,1),(4,'student_absent_attendence','0','0',0,1,1,1,1,0,1,1,NULL,'Absent Attendence','student_absent_attendence','Dear Parent, this is an important attendance alert from Your School Name.\r\n\r\nWe regret to inform you that your ward {{student_name}} was marked Absent on {{date}}.\r\n\r\nThe absence was recorded for the following period:\r\nSubject: {{subject_name}}\r\nSubject Code: {{subject_code}}\r\nSubject Type: {{subject_type}}\r\n\r\nKindly ensure your ward attends classes regularly. If this absence was due to a medical or personal reason, please submit a leave letter to the class teacher at the earliest.\r\n\r\nThank you for your cooperation.','{{student_name}} {{mobileno}} {{email}} {{father_name}} {{father_phone}} {{father_occupation}} {{mother_name}} {{mother_phone}} {{guardian_name}} {{guardian_phone}} {{guardian_occupation}} {{guardian_email}} {{date}} {{current_session_name}} {{time_from}} {{time_to}} {{subject_name}} {{subject_code}} {{subject_type}}  ',NULL,'2022-12-28 09:52:24','2026-04-14 11:16:56',0,1),(6,'homework','1','0',0,1,1,1,1,0,1,1,NULL,'Homework','homework','Dear Parent, a new homework has been assigned to your ward {{student_name}} by Your School Name.\r\n\r\nPlease take note of the following details:\r\n\r\nClass: {{class}} | Section: {{section}}\r\nSubject: {{subject}}\r\nHomework Assigned Date: {{homework_date}}\r\nLast Date to Submit: {{submit_date}}\r\n\r\nKindly ensure your ward completes and submits the homework on or before the due date mentioned above. Late submissions may not be accepted.\r\n\r\nThank you for your support.','{{homework_date}} {{submit_date}} {{class}} {{section}} {{subject}} {{student_name}} {{admission_no}} ',NULL,'2022-12-28 09:52:24','2026-04-14 14:23:49',0,1),(7,'fees_reminder','1','0',0,1,1,1,1,0,1,1,NULL,'Fees Reminder','fees_reminder','Dear Parent, this is a gentle reminder from Your School Name regarding the pending fee payment for your ward {{student_name}}.\r\n\r\nFee Details:\r\nFee Type: {{fee_type}}\r\nOutstanding Amount: Rs. {{due_amount}}\r\nLast Date to Pay: {{due_date}}\r\n\r\nKindly make the payment on or before the due date to avoid any late fine charges. Please ignore this message if you have already made the payment.\r\n\r\nFor any queries regarding your fee account, please contact the college accounts section during working hours.\r\n\r\nThank you.','{{fee_type}}{{fee_code}}{{due_date}}{{student_name}}{{school_name}}{{fee_amount}}{{due_amount}}{{deposit_amount}} ',NULL,'2022-12-28 09:52:24','2026-04-14 14:26:18',0,1),(8,'forgot_password','1','0',0,0,0,1,1,1,1,1,1,'Forgot Password','forgot_password','Dear  {{name}} , \r\n    Recently a request was submitted to reset password for your account. If you didn\'t make the request, just ignore this email. Otherwise you can reset your password using this link <a href=\'{{resetPassLink}}\'>Click here to reset your password</a>,\r\nif you\'re having trouble clicking the password reset button, copy and paste the URL below into your web browser. your username {{username}}\r\n{{resetPassLink}}\r\n Regards,\r\n {{school_name}}','{{school_name}}{{name}}{{username}}{{resetPassLink}} ',NULL,'2022-12-28 09:52:24','2026-02-24 08:31:42',0,1),(9,'online_examination_publish_exam','1','0',0,1,1,1,1,0,1,1,NULL,'Online Examination Publish Exam','online_examination_publish_exam','Dear Student, this is a notification from Your School Name regarding an upcoming online examination.\r\n\r\nA new exam has been scheduled for you. Please find the details below:\r\n\r\nExam Title: {{exam_title}}\r\nDuration: {{time_duration}} minutes\r\nAvailable From: {{exam_from}}\r\nAvailable Until: {{exam_to}}\r\n\r\nKindly ensure you are prepared and log in to the student portal within the above mentioned time window to attempt the exam. Late access may not be permitted after the exam window closes.\r\n\r\nAll the best!','{{exam_title}} {{exam_from}} {{exam_to}} {{time_duration}} {{attempt}} {{passing_percentage}}',NULL,'2022-12-28 09:52:24','2026-04-14 14:36:28',0,1),(10,'online_examination_publish_result','1','0',0,1,1,1,1,0,1,1,NULL,'Online Examination Publish Result','online_examination_publish_result','Dear Student, we are pleased to inform you that the results for your recently conducted examination have been declared by Your School Name.\r\n\r\nExam Title: {{exam_title}}\r\nExam Period: {{exam_from}} to {{exam_to}}\r\n\r\nYour result is now available on the student portal. Kindly log in to your account and navigate to the Examination section to view your detailed score and performance report.\r\n\r\nFor any queries or discrepancies regarding your result, please contact the examination department during college working hours.\r\n\r\nCongratulations and best wishes for your future!','{{exam_title}} {{exam_from}} {{exam_to}} {{time_duration}} {{attempt}} {{passing_percentage}}',NULL,'2022-12-28 09:52:24','2026-04-14 14:39:07',0,1),(11,'online_admission_form_submission','1','0',0,1,1,1,1,0,1,1,NULL,'Online Admission Form Submission','online_admission_form_submission','Dear {{firstname}}  {{lastname}},\r\n\r\nGreetings from Meenakshi College Of Engineering, KK Nagar, Chennai!!!\r\n\r\n Your online admission form is Submitted successfully  on date {{date}}. Your Reference number is {{reference_no}}. Please remember your reference number for further process.\r\n\r\nFor Any Queries Contact Admission Office: 8925977077\r\n\r\nRegards,\r\nTeam Admissions.',' {{firstname}} {{lastname}} {{date}} {{reference_no}}',NULL,'2022-12-28 09:52:24','2026-02-24 08:31:42',0,1),(12,'online_admission_fees_submission','1','0',0,1,1,1,1,0,1,1,NULL,'Online Admission Fees Submission','online_admission_fees_submission','Dear {{firstname}}  {{lastname}} your online admission form is Submitted successfully and the payment of {{paid_amount}} has recieved successfully on date {{date}}. Your Reference number is {{reference_no}}. Please remember your reference number for further process.',' {{firstname}} {{lastname}} {{date}} {{paid_amount}} {{reference_no}}',NULL,'2022-12-28 09:52:24','2026-02-24 08:31:42',0,1),(13,'student_login_credential','1','0',0,0,1,1,1,0,1,1,NULL,'Student Login Credential','student_login_credential','Dear {{display_name}}, welcome to Your School Name! Your student portal account has been created successfully.\r\n\r\nPlease find your login credentials below:\r\n\r\nAdmission No: {{admission_no}}\r\nUsername: {{username}}\r\nTemporary Password: {{password}}\r\n\r\nYou can log in to the student portal at: https://mcekknagar.ac.in\r\n\r\nFor security purposes, we strongly recommend changing your password immediately after your first login. Please do not share your login credentials with anyone.\r\n\r\nFor any login issues, please contact the college administration office.\r\n\r\nWelcome aboard!','{{url}} {{display_name}} {{username}} {{password}} {{admission_no}}',NULL,'2022-08-06 05:34:41','2026-04-14 14:44:10',0,1),(14,'staff_login_credential','1','0',0,0,1,0,0,1,NULL,NULL,1,'Staff Login Credential','staff_login_credential','Hello {{first_name}} {{last_name}} your login details for Url: {{url}} Username: {{username}}  Password: {{password}} Employee ID: {{employee_id}}','{{url}} {{first_name}} {{last_name}} {{username}} {{password}} {{employee_id}}',NULL,'2021-12-23 11:59:13','2026-02-24 08:31:42',0,1),(15,'fee_processing','1','0',0,1,1,1,1,0,1,1,NULL,'Fee processing','fee_processing','Dear Parent, we are pleased to confirm that the fee payment has been successfully received for your ward by Your School Name.\r\n\r\nPayment Details:\r\nStudent Name: {{student_name}}\r\nClass: {{class}} | Section: {{section}}\r\nAmount Paid: Rs. {{fee_amount}}\r\nTransaction ID: {{transaction_id}}\r\n\r\nPlease keep this message as your digital acknowledgement of payment. For a detailed fee receipt, kindly visit the college accounts section or log in to the student portal.\r\n\r\nThank you for your prompt payment.','{{student_name}} {{class}} {{section}} {{email}} {{contact_no}} {{transaction_id}} {{fee_amount}}',NULL,'2021-12-22 10:15:42','2026-04-14 14:50:41',0,1),(16,'online_admission_fees_processing','1','0',0,1,1,1,1,0,1,1,NULL,'Online Admission Fees Processing','online_admission_fees_processing','Dear {{firstname}}  {{lastname}} your online admission form is Submitted successfully and the payment of {{paid_amount}} has processing on date {{date}}. Your Reference number is {{reference_no}} and your transaction id {{transaction_id}}. Please remember your reference number for further process.',' {{firstname}} {{lastname}} {{date}} {{paid_amount}} {{reference_no}} {{transaction_id}}',NULL,'2022-08-06 11:09:47','2026-02-24 08:31:42',0,1),(17,'student_apply_leave','1','0',0,0,1,0,1,1,NULL,1,1,'Student Apply Leave ( {{student_name}} - {{admission_no}} )','student_apply_leave','My Name is {{student_name}} Class {{class}} section {{section}}. I have to apply leave on {{apply_date}}and from {{from_date}} to {{to_date}}. {{message}} please provide.','{{message}} {{apply_date}} {{from_date}} {{to_date}} {{student_name}} {{class}} {{section}}',NULL,'2022-03-12 11:58:37','2026-02-24 08:31:42',0,1),(18,'email_pdf_exam_marksheet','1','0',0,0,0,1,1,0,1,1,NULL,'Email PDF Exam Marksheet ( {{student_name}} - {{admission_no}} )','email_pdf_exam_marksheet','Dear {{student_name}}, this is a notification from Your School Name regarding your examination marksheet.\r\n\r\nStudent Details:\r\nAdmission No: {{admission_no}}\r\nClass: {{class}} | Section: {{section}}\r\nRoll No: {{roll_no}}\r\n\r\nYour marksheet for the examination *{{exam}}* has been generated and sent to your registered email address. Kindly check your inbox and download the marksheet for your records.\r\n\r\nIf you have not received the email or need a physical copy, please contact the examination department at the college during working hours.\r\n\r\nThank you.','{{student_name}} {{class}}  {{section}}  {{admission_no}}  {{roll_no}} {{exam}} {{admit_card_roll_no}} ',NULL,'2022-03-12 12:24:42','2026-04-14 14:57:04',0,1),(19,'homework_evaluation','1','0',0,1,1,1,1,0,1,1,NULL,'Homework Evaluation','homework_evaluation','Dear Parent, we are pleased to share the homework evaluation details for your ward.\r\n\r\nStudent Name: {{student_name}}\r\nAdmission No: {{admission_no}}\r\nClass: {{class}} | Section: {{section}}\r\n\r\nSubject: {{subject}}\r\nHomework Assigned on: {{homework_date}}\r\nLast Date to Submit: {{submit_date}}\r\n\r\nYour child\'s homework was evaluated on {{evaluation_date}} and has been awarded a score of {{marks}} out of {{max_marks}} marks.\r\n\r\nThank you for your continued support in your child\'s learning journey.','{{homework_date}} {{submit_date}} {{class}} {{section}} {{subject}} {{student_name}} {{admission_no}} {{max_marks}} {{marks}} {{evaluation_date}}',120,'2025-01-15 08:00:34','2026-04-14 10:45:41',0,1),(20,'student_present_attendence','1','0',0,1,1,1,1,0,1,1,NULL,'Present Attendence','student_present_attendence','Dear Parent, this is to inform you that your ward {{student_name}} (Admission No: {{admission_no}}) was marked Present on {{date}}.\r\n\r\nAttendance recorded at {{in_time}} for the following period:\r\n\r\nSubject: {{subject_name}} (Code: {{subject_code}} | Type: {{subject_type}})\r\nPeriod Timing: {{period_time_from}} to {{period_time_to}}\r\n\r\nPlease contact the college administration for any queries regarding your ward\'s attendance record.\r\n\r\nThank you.','{{student_name}} {{mobileno}} {{email}} {{father_name}} {{father_phone}} {{father_occupation}} {{mother_name}} {{mother_phone}} {{guardian_name}} {{guardian_phone}} {{guardian_occupation}} {{guardian_email}} {{date}} {{in_time}}  ({{admission_no}}) {{period_time_from}} {{period_time_to}} {{subject_name}} {{subject_code}} {{subject_type}}',15,'2025-01-13 05:55:46','2026-04-14 10:51:28',0,1),(21,'staff_present_attendence','0','0',0,1,1,0,0,0,NULL,NULL,1,'staff Present Attendence','staff_present_attendence','Present Notice: Staff Name {{staff_name}} ({{employee_id}}) is Absent on Date : {{date}}\r\nstaff contact no:{{contact_no}}\r\nstaff mail id : {{email}}, please contact admin office for further clarification.','{{date}} {{in_time}} {{staff_name}} {{employee_id}} {{contact_no}} {{email}}\n',190,'2025-02-07 11:43:28','2026-04-14 10:46:10',0,1),(22,'staff_absent_attendence','0','0',0,1,1,0,0,0,NULL,NULL,1,'staff Absent Attendence','staff_absent_attendence','Absent Notice: Staff Name {{staff_name}} ({{employee_id}}) is Absent on Date : {{date}} \r\n<br>\r\nstaff contact no:{{contact_no}}\r\n<br>\r\nstaff mail id : {{email}}, please contact admin office for further clarification.','{{date}} {{staff_name}} {{employee_id}} {{contact_no}} {{email}}\n',200,'2025-02-07 11:43:28','2026-04-14 10:34:12',0,1),(23,'enquiry_form_submission','1','0',0,0,1,0,0,0,NULL,NULL,NULL,'Enquiry Form Submission','enquiry_form_submission','Dear {{name}} your enquiry has been submitted successfully on {{date}}. Your Reference number is {{reference_no}}. Please remember your reference number for further process.','',200,'2025-02-07 11:43:28','2026-04-11 12:24:06',1,1),(24,'cbse_email_pdf_exam_marksheet','1','1',1,1,1,1,1,0,1,1,NULL,'CBSE Exam Marksheet PDF ( {{student_name}} - {{admission_no}} )','cbse_email_pdf_exam_marksheet','Dear {{student_name}}, this is a notification from Your School Name regarding your examination marksheet.\r\n\r\nStudent Details:\r\nAdmission No: {{admission_no}}\r\nClass: {{class}} | Section: {{section}}\r\nRoll No: {{roll_no}}\r\n\r\nYour marksheet has been generated and sent to your registered email address. Kindly check your inbox and download it for your records.\r\n\r\nIf you have not received the email or need a physical copy, please contact the examination department at the college during working hours.\r\n\r\nThank you.','{{student_name}} {{class}} {{section}} {{admission_no}} {{roll_no}}',NULL,'2023-06-21 07:59:44','2026-04-14 14:59:38',0,0),(25,'cbse_exam_result','1','1',1,1,1,1,1,0,1,1,NULL,'CBSE Exam Result','cbse_exam_result','Dear {{student_name}} - {{roll_no}}, your {{exam}} result has been published.','{{student_name}} {{roll_no}} {{exam}} {{exam_marksheet_url}}',NULL,'2023-06-21 07:59:47','2026-04-10 13:13:54',0,1),(26,'online_classes','1','1',1,1,1,1,1,0,1,1,NULL,'Zoom Online Classes','online_classes','Dear student, your live class {{title}} has been scheduled on {{date}} for the duration of {{duration}} minute, please do not share the link to any body.','{{title}} {{date}} {{duration}}',NULL,'2022-07-13 08:01:48','2026-04-10 13:13:54',0,1),(27,'online_meeting','1','1',0,0,1,0,0,1,NULL,NULL,1,'Zoom Online Meeting','online_meeting','Dear staff, your live meeting {{title}} has been scheduled on {{date}} for the duration of {{duration}} minute, please do not share the link to any body.','{{title}} {{date}} {{duration}} {{employee_id}} {{department}} {{designation}} {{name}} {{contact_no}} {{email}}',NULL,'2022-07-13 07:46:54','2026-04-10 13:13:54',0,1),(28,'zoom_online_classes_start','1','1',1,1,1,1,1,0,1,1,NULL,'Zoom Online  Classes Start','zoom_online_classes_start','Dear student, your live class {{title}} has been started  for the duration of {{duration}} minute.','{{title}} {{date}} {{duration}}',NULL,'2022-07-13 08:04:02','2026-04-10 13:13:54',0,1),(29,'zoom_online_meeting_start','1','1',0,0,1,0,0,1,NULL,NULL,1,'Zoom Live Meeting Start','zoom_online_meeting_start','Dear {{name}},  your live meeting {{title}}  has been started  for the duration of {{duration}} minute.','{{title}} {{date}} {{duration}} {{employee_id}} {{department}} {{designation}} {{name}} {{contact_no}} {{email}}',NULL,'2022-07-11 07:39:06','2026-04-10 13:13:54',0,1);
/*!40000 ALTER TABLE `notification_setting` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `online_admission_fields`
--

LOCK TABLES `online_admission_fields` WRITE;
/*!40000 ALTER TABLE `online_admission_fields` DISABLE KEYS */;
INSERT INTO `online_admission_fields` VALUES (1,'middlename',0,'2021-05-28 10:29:23','2025-09-25 15:40:51'),(2,'lastname',1,'2021-06-02 04:49:19','2025-09-25 15:40:51'),(3,'category',0,'2021-06-02 04:48:35','2025-09-25 15:40:51'),(4,'religion',0,'2021-06-02 04:48:35','2025-09-25 15:40:51'),(5,'cast',0,'2021-06-02 04:48:35','2025-09-25 15:40:51'),(6,'mobile_no',1,'2021-06-02 04:50:24','2025-09-25 15:40:51'),(7,'admission_date',0,'2021-06-02 04:48:35','2025-09-25 15:40:51'),(8,'student_photo',0,'2021-06-02 04:48:35','2025-09-25 15:40:51'),(9,'is_student_house',0,'2021-05-29 13:22:53','2025-09-25 15:40:51'),(10,'is_blood_group',0,'2021-06-02 04:48:35','2025-09-25 15:40:51'),(11,'student_height',0,'2021-06-02 04:48:35','2025-09-25 15:40:51'),(12,'student_weight',0,'2021-06-02 04:48:35','2025-09-25 15:40:51'),(13,'father_name',0,'2021-06-02 04:48:35','2025-09-25 15:40:51'),(14,'father_phone',0,'2021-06-02 04:48:35','2025-09-25 15:40:51'),(15,'father_occupation',0,'2021-06-02 04:48:35','2025-09-25 15:40:51'),(16,'father_pic',0,'2021-06-02 04:48:35','2025-09-25 15:40:51'),(17,'mother_name',0,'2021-06-02 04:48:35','2025-09-25 15:40:51'),(18,'mother_phone',0,'2021-06-02 04:48:35','2025-09-25 15:40:51'),(19,'mother_occupation',0,'2021-06-02 04:48:35','2025-09-25 15:40:51'),(20,'mother_pic',0,'2021-06-02 04:48:35','2025-09-25 15:40:51'),(21,'guardian_name',1,'2021-06-02 04:50:54','2025-09-25 15:40:51'),(22,'guardian_phone',1,'2021-06-02 04:50:54','2025-09-25 15:40:51'),(23,'if_guardian_is',1,'2021-06-02 04:50:54','2025-09-25 15:40:51'),(24,'guardian_relation',1,'2021-06-02 04:50:54','2025-09-25 15:40:51'),(25,'guardian_email',1,'2021-06-02 04:51:35','2025-09-25 15:40:51'),(26,'guardian_occupation',1,'2021-06-02 04:51:26','2025-09-25 15:40:51'),(27,'guardian_address',1,'2021-06-02 04:51:31','2025-09-25 15:40:51'),(28,'bank_account_no',0,'2021-06-02 04:48:35','2025-09-25 15:40:51'),(29,'bank_name',0,'2021-06-02 04:48:35','2025-09-25 15:40:51'),(30,'ifsc_code',0,'2021-06-02 04:48:35','2025-09-25 15:40:51'),(31,'national_identification_no',0,'2021-06-02 04:48:35','2025-09-25 15:40:51'),(32,'local_identification_no',0,'2021-06-02 04:48:35','2025-09-25 15:40:51'),(33,'rte',0,'2021-06-02 04:48:35','2025-09-25 15:40:51'),(34,'previous_school_details',0,'2021-06-02 04:48:35','2025-09-25 15:40:51'),(35,'guardian_photo',1,'2021-06-02 04:51:29','2025-09-25 15:40:51'),(36,'student_note',0,'2021-06-02 04:55:08','2025-09-25 15:40:51'),(37,'measurement_date',0,'2021-06-02 04:48:35','2025-09-25 15:40:51'),(38,'student_email',1,'2021-06-02 04:49:38','2025-09-25 15:40:51'),(39,'current_address',0,'2021-06-02 04:48:35','2025-09-25 15:40:51'),(40,'permanent_address',0,'2021-06-02 04:48:35','2025-09-25 15:40:51'),(41,'upload_documents',1,'2022-09-20 08:00:32','2025-09-25 15:40:51');
/*!40000 ALTER TABLE `online_admission_fields` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `payment_settings`
--

LOCK TABLES `payment_settings` WRITE;
/*!40000 ALTER TABLE `payment_settings` DISABLE KEYS */;
INSERT INTO `payment_settings` VALUES (1,'razorpay','','','','','','','','','','no',0,'','','percentage','2','2025-12-31 06:30:00','2026-04-12 07:15:20'),(2,'stripe','','','','','','','','','','no',0,'','','none','','2025-12-31 06:44:35','2026-04-12 07:15:20'),(3,'billdesk','','','','','','','','','','no',1,'','','percentage','2','2026-01-12 04:47:28','2026-04-12 07:15:20');
/*!40000 ALTER TABLE `payment_settings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `payroll_allowance_types`
--

LOCK TABLES `payroll_allowance_types` WRITE;
/*!40000 ALTER TABLE `payroll_allowance_types` DISABLE KEYS */;
INSERT INTO `payroll_allowance_types` VALUES (1,'BASIC','Basic Salary','earning',1,0,1,1,'Base salary component - typically 40-50% of CTC','2026-02-14 18:45:25','2026-02-14 18:45:25'),(2,'DA','Dearness Allowance','earning',1,0,1,2,'Cost of living adjustment based on inflation index','2026-02-14 18:45:25','2026-02-14 18:45:25'),(3,'HRA','House Rent Allowance','earning',1,0,1,3,'Housing benefit - partially tax exempt (least of: actual HRA, rent-10% basic, 50%/40% basic for metro/non-metro)','2026-02-14 18:45:25','2026-02-14 18:45:25'),(4,'SA','Special Allowance','earning',1,0,1,4,'Miscellaneous/residual salary component','2026-02-14 18:45:25','2026-02-14 18:45:25'),(5,'CONV','Conveyance Allowance','earning',0,0,1,5,'Transport allowance - exempt up to Rs 1,600/month (Rs 19,200/year)','2026-02-14 18:45:25','2026-02-14 18:45:25'),(6,'MED','Medical Allowance','earning',0,0,1,6,'Medical reimbursement - exempt up to Rs 15,000/year','2026-02-14 18:45:25','2026-02-14 18:45:25'),(7,'LTA','Leave Travel Allowance','earning',0,0,1,7,'Travel expense exemption - 2 journeys in 4 years','2026-02-14 18:45:25','2026-02-14 18:45:25'),(8,'MOBILE','Mobile/Telephone Allowance','earning',0,0,1,8,'Communication expenses - fully exempt','2026-02-14 18:45:25','2026-02-14 18:45:25'),(9,'MEAL','Meal/Food Allowance','earning',0,0,1,9,'Free meals/coupons - exempt up to Rs 50/meal','2026-02-14 18:45:25','2026-02-14 18:45:25'),(10,'BONUS','Performance Bonus','earning',1,0,1,10,'Annual or monthly performance-based bonus','2026-02-14 18:45:25','2026-02-14 18:45:25'),(11,'INCENTIVE','Sales Incentive','earning',1,0,1,11,'Target-based sales/performance incentive','2026-02-14 18:45:25','2026-02-14 18:45:25'),(12,'OVERTIME','Overtime Pay','earning',1,0,1,12,'Extra hours compensation beyond regular shift','2026-02-14 18:45:25','2026-02-14 18:45:25'),(13,'SHIFT','Shift Allowance','earning',1,0,1,13,'Night/weekend shift differential pay','2026-02-14 18:45:25','2026-02-14 18:45:25'),(14,'ARREARS','Arrears','earning',1,0,1,14,'Backpay from previous months','2026-02-14 18:45:25','2026-02-14 18:45:25'),(15,'EDUCATION','Education Allowance','earning',0,0,1,15,'Children education - exempt up to Rs 100/month per child (max 2)','2026-02-14 18:45:25','2026-02-14 18:45:25'),(16,'UNIFORM','Uniform Allowance','earning',0,0,1,16,'Uniform/livery expenses - fully exempt','2026-02-14 18:45:25','2026-02-14 18:45:25'),(17,'OTHER_EARN','Other Earnings','earning',1,0,1,17,'Miscellaneous earnings not categorized above','2026-02-14 18:45:25','2026-02-14 18:45:25'),(18,'AGP','Academic Grade Pay','earning',1,0,1,18,'Academic Grade Pay for teaching staff based on academic qualifications and experience','2026-02-16 09:19:35','2026-02-16 09:19:35'),(50,'EPF','EPF Employee Contribution','deduction',0,1,1,50,'Employee Provident Fund - 12% of basic+DA (auto-calculated)','2026-02-14 18:45:25','2026-02-14 18:45:25'),(51,'ESI','ESI Employee Contribution','deduction',0,1,1,51,'Employee State Insurance - 0.75% of gross up to Rs 21,000 wage ceiling (auto-calculated)','2026-02-14 18:45:25','2026-02-14 18:45:25'),(52,'TDS','Income Tax (TDS)','deduction',0,1,1,52,'Tax Deducted at Source - as per New Tax Regime (auto-calculated)','2026-02-14 18:45:25','2026-02-14 18:45:25'),(53,'PT','Professional Tax','deduction',0,1,1,53,'State-specific professional tax (varies by state)','2026-02-14 18:45:25','2026-02-14 18:45:25'),(60,'LOAN','Loan Repayment','deduction',0,0,1,60,'Salary advance or loan EMI recovery','2026-02-14 18:45:25','2026-02-14 18:45:25'),(61,'ADVANCE','Advance Recovery','deduction',0,0,1,61,'Recovery of advance payments','2026-02-14 18:45:25','2026-02-14 18:45:25'),(62,'INSURANCE','Insurance Premium','deduction',0,0,1,62,'Group health/life insurance premium','2026-02-14 18:45:25','2026-02-14 18:45:25'),(63,'PF_LOAN','PF Loan Recovery','deduction',0,0,1,63,'EPF loan repayment deduction','2026-02-14 18:45:25','2026-02-14 18:45:25'),(64,'LOP','Loss of Pay','deduction',0,0,1,64,'Unpaid leave deduction (auto-calculated by attendance)','2026-02-14 18:45:25','2026-02-14 18:45:25'),(65,'LATE','Late Coming Fine','deduction',0,0,1,65,'Penalty for late attendance','2026-02-14 18:45:25','2026-02-14 18:45:25'),(66,'ABSENT','Absence Deduction','deduction',0,0,1,66,'Deduction for unauthorized absence','2026-02-14 18:45:25','2026-02-14 18:45:25'),(67,'CANTEEN','Canteen/Meal Deduction','deduction',0,0,1,67,'Company cafeteria/meal charges','2026-02-14 18:45:25','2026-02-14 18:45:25'),(68,'TRANSPORT','Transport Deduction','deduction',0,0,1,68,'Company transport service charges','2026-02-14 18:45:25','2026-02-14 18:45:25'),(69,'UNIFORM_DED','Uniform Deduction','deduction',0,0,1,69,'Uniform purchase/replacement cost','2026-02-14 18:45:25','2026-02-14 18:45:25'),(70,'OTHER_DED','Other Deduction','deduction',0,0,1,70,'Miscellaneous deductions not categorized above','2026-02-14 18:45:25','2026-02-14 18:45:25'),(71,'TEMP','Temporary Increment','earning',1,0,1,18,'One-time salary increment for specified month - appears only in current month, then merged into Special Allowance','2026-02-17 04:22:11','2026-02-17 04:22:11');
/*!40000 ALTER TABLE `payroll_allowance_types` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `payroll_settings`
--

LOCK TABLES `payroll_settings` WRITE;
/*!40000 ALTER TABLE `payroll_settings` DISABLE KEYS */;
INSERT INTO `payroll_settings` VALUES (1,'new',1,15000.00,1,75000.00,'2026-02-11 19:58:22','2026-02-11 19:58:22');
/*!40000 ALTER TABLE `payroll_settings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `permission_category`
--

LOCK TABLES `permission_category` WRITE;
/*!40000 ALTER TABLE `permission_category` DISABLE KEYS */;
INSERT INTO `permission_category` VALUES (1,1,'Student','student',1,1,1,1,'2019-10-24 05:42:03','2025-09-25 15:40:51'),(2,1,'Import Student','import_student',1,0,0,0,'2018-06-22 10:17:19','2025-09-25 15:40:51'),(3,1,'Student Categories','student_categories',1,1,1,1,'2018-06-22 10:17:36','2025-09-25 15:40:51'),(4,1,'Student Houses','student_houses',1,1,1,1,'2018-06-22 10:17:53','2025-09-25 15:40:51'),(5,2,'Collect Fees','collect_fees',1,1,0,1,'2018-06-22 10:21:03','2025-09-25 15:40:51'),(6,2,'Fees Carry Forward','fees_carry_forward',1,0,0,0,'2018-06-27 00:18:15','2025-09-25 15:40:51'),(7,2,'Fees Master','fees_master',1,1,1,1,'2018-06-27 00:18:57','2025-09-25 15:40:51'),(8,2,'Fees Group','fees_group',1,1,1,1,'2018-06-22 10:21:46','2025-09-25 15:40:51'),(9,3,'Income','income',1,1,1,1,'2018-06-22 10:23:21','2025-09-25 15:40:51'),(10,3,'Income Head','income_head',1,1,1,1,'2018-06-22 10:22:44','2025-09-25 15:40:51'),(11,3,'Search Income','search_income',1,0,0,0,'2018-06-22 10:23:00','2025-09-25 15:40:51'),(12,4,'Expense','expense',1,1,1,1,'2018-06-22 10:24:06','2025-09-25 15:40:51'),(13,4,'Expense Head','expense_head',1,1,1,1,'2018-06-22 10:23:47','2025-09-25 15:40:51'),(14,4,'Search Expense','search_expense',1,0,0,0,'2018-06-22 10:24:13','2025-09-25 15:40:51'),(15,5,'Student / Period Attendance','student_attendance',1,1,1,0,'2019-11-29 01:19:05','2025-09-25 15:40:51'),(20,6,'Marks Grade','marks_grade',1,1,1,1,'2018-06-22 10:25:25','2025-09-25 15:40:51'),(21,7,'Class Timetable','class_timetable',1,0,1,0,'2019-11-24 03:05:17','2025-09-25 15:40:51'),(23,7,'Subject','subject',1,1,1,1,'2018-06-22 10:32:17','2025-09-25 15:40:51'),(24,7,'Class','class',1,1,1,1,'2018-06-22 10:32:35','2025-09-25 15:40:51'),(25,7,'Section','section',1,1,1,1,'2018-06-22 10:31:10','2025-09-25 15:40:51'),(26,7,'Promote Student','promote_student',1,0,0,0,'2018-06-22 10:32:47','2025-09-25 15:40:51'),(27,8,'Upload Content','upload_content',1,1,0,1,'2018-06-22 10:33:19','2025-09-25 15:40:51'),(31,10,'Issue Item','issue_item',1,1,1,1,'2019-11-29 06:39:27','2025-09-25 15:40:51'),(32,10,'Add Item Stock','item_stock',1,1,1,1,'2019-11-24 00:39:17','2025-09-25 15:40:51'),(33,10,'Add Item','item',1,1,1,1,'2019-11-24 00:39:39','2025-09-25 15:40:51'),(34,10,'Item Store','store',1,1,1,1,'2019-11-24 00:40:41','2025-09-25 15:40:51'),(35,10,'Item Supplier','supplier',1,1,1,1,'2019-11-24 00:40:49','2025-09-25 15:40:51'),(37,11,'Routes','routes',1,1,1,1,'2018-06-22 10:39:17','2025-09-25 15:40:51'),(38,11,'Vehicle','vehicle',1,1,1,1,'2018-06-22 10:39:36','2025-09-25 15:40:51'),(39,11,'Assign Vehicle','assign_vehicle',1,1,1,1,'2018-06-27 04:39:20','2025-09-25 15:40:51'),(40,12,'Hostel','hostel',1,1,1,1,'2018-06-22 10:40:49','2025-09-25 15:40:51'),(41,12,'Room Type','room_type',1,1,1,1,'2018-06-22 10:40:27','2025-09-25 15:40:51'),(42,12,'Hostel Rooms','hostel_rooms',1,1,1,1,'2018-06-25 06:23:03','2025-09-25 15:40:51'),(43,13,'Notice Board','notice_board',1,1,1,1,'2018-06-22 10:41:17','2025-09-25 15:40:51'),(44,13,'Email','email',1,0,0,0,'2019-11-26 05:20:37','2025-09-25 15:40:51'),(46,13,'Email / SMS Log','email_sms_log',1,0,0,0,'2018-06-22 10:41:23','2025-09-25 15:40:51'),(53,15,'Languages','languages',0,1,0,1,'2021-01-23 07:09:32','2025-09-25 15:40:51'),(54,15,'General Setting','general_setting',1,0,1,0,'2018-07-05 09:08:35','2025-09-25 15:40:51'),(55,15,'Session Setting','session_setting',1,1,1,1,'2018-06-22 10:44:15','2025-09-25 15:40:51'),(56,15,'Notification Setting','notification_setting',1,0,1,0,'2018-07-05 09:08:41','2025-09-25 15:40:51'),(57,15,'SMS Setting','sms_setting',1,0,1,0,'2018-07-05 09:08:47','2025-09-25 15:40:51'),(58,15,'Email Setting','email_setting',1,0,1,0,'2018-07-05 09:08:51','2025-09-25 15:40:51'),(59,15,'Front CMS Setting','front_cms_setting',1,0,1,0,'2018-07-05 09:08:55','2025-09-25 15:40:51'),(60,15,'Payment Methods','payment_methods',1,0,1,0,'2018-07-05 09:08:59','2025-09-25 15:40:51'),(61,16,'Menus','menus',1,1,0,1,'2018-07-09 03:50:06','2025-09-25 15:40:51'),(62,16,'Media Manager','media_manager',1,1,0,1,'2018-07-09 03:50:26','2025-09-25 15:40:51'),(63,16,'Banner Images','banner_images',1,1,0,1,'2018-06-22 10:46:02','2025-09-25 15:40:51'),(64,16,'Pages','pages',1,1,1,1,'2018-06-22 10:46:21','2025-09-25 15:40:51'),(65,16,'Gallery','gallery',1,1,1,1,'2018-06-22 10:47:02','2025-09-25 15:40:51'),(66,16,'Event','event',1,1,1,1,'2018-06-22 10:47:20','2025-09-25 15:40:51'),(67,16,'News','notice',1,1,1,1,'2018-07-03 08:39:34','2025-09-25 15:40:51'),(68,2,'Fees Group Assign','fees_group_assign',1,0,0,0,'2018-06-22 10:20:42','2025-09-25 15:40:51'),(69,2,'Fees Type','fees_type',1,1,1,1,'2018-06-22 10:19:34','2025-09-25 15:40:51'),(70,2,'Fees Discount','fees_discount',1,1,1,1,'2018-06-22 10:20:10','2025-09-25 15:40:51'),(71,2,'Fees Discount Assign','fees_discount_assign',1,0,0,0,'2018-06-22 10:20:17','2025-09-25 15:40:51'),(73,2,'Search Fees Payment','search_fees_payment',1,0,0,0,'2018-06-22 10:20:27','2025-09-25 15:40:51'),(74,2,'Search Due Fees','search_due_fees',1,0,0,0,'2018-06-22 10:20:35','2025-09-25 15:40:51'),(77,7,'Assign Class Teacher','assign_class_teacher',1,1,1,1,'2018-06-22 10:30:52','2025-09-25 15:40:51'),(78,17,'Admission Enquiry','admission_enquiry',1,1,1,1,'2018-06-22 10:51:24','2025-09-25 15:40:51'),(79,17,'Follow Up Admission Enquiry','follow_up_admission_enquiry',1,1,0,1,'2018-06-22 10:51:39','2025-09-25 15:40:51'),(80,17,'Visitor Book','visitor_book',1,1,1,1,'2018-06-22 10:48:58','2025-09-25 15:40:51'),(81,17,'Phone Call Log','phone_call_log',1,1,1,1,'2018-06-22 10:50:57','2025-09-25 15:40:51'),(82,17,'Postal Dispatch','postal_dispatch',1,1,1,1,'2018-06-22 10:50:21','2025-09-25 15:40:51'),(83,17,'Postal Receive','postal_receive',1,1,1,1,'2018-06-22 10:50:04','2025-09-25 15:40:51'),(84,17,'Complain','complaint',1,1,1,1,'2018-07-03 08:40:55','2025-09-25 15:40:51'),(85,17,'Setup Front Office','setup_font_office',1,1,1,1,'2025-02-13 09:03:14','2025-09-25 15:40:51'),(86,18,'Staff','staff',1,1,1,1,'2018-06-22 10:53:31','2025-09-25 15:40:51'),(87,18,'Disable Staff','disable_staff',1,0,0,0,'2018-06-22 10:53:12','2025-09-25 15:40:51'),(88,18,'Staff Attendance','staff_attendance',1,1,1,0,'2018-06-22 10:53:10','2025-09-25 15:40:51'),(90,18,'Staff Payroll','staff_payroll',1,1,0,1,'2018-06-22 10:52:51','2025-09-25 15:40:51'),(93,19,'Homework','homework',1,1,1,1,'2018-06-22 10:53:50','2025-09-25 15:40:51'),(94,19,'Homework Evaluation','homework_evaluation',1,1,0,0,'2018-06-27 03:07:21','2025-09-25 15:40:51'),(96,20,'Student Certificate','student_certificate',1,1,1,1,'2018-07-06 10:41:07','2025-09-25 15:40:51'),(97,20,'Generate Certificate','generate_certificate',1,0,0,0,'2018-07-06 10:37:16','2025-09-25 15:40:51'),(98,20,'Student ID Card','student_id_card',1,1,1,1,'2018-07-06 10:41:28','2025-09-25 15:40:51'),(99,20,'Generate ID Card','generate_id_card',1,0,0,0,'2018-07-06 10:41:49','2025-09-25 15:40:51'),(102,21,'Calendar To Do List','calendar_to_do_list',1,1,1,1,'2018-06-22 10:54:41','2025-09-25 15:40:51'),(104,10,'Item Category','item_category',1,1,1,1,'2018-06-22 10:34:33','2025-09-25 15:40:51'),(106,22,'Quick Session Change','quick_session_change',1,0,0,0,'2018-06-22 10:54:45','2025-09-25 15:40:51'),(107,1,'Disable Student','disable_student',1,0,0,0,'2018-06-25 06:21:34','2025-09-25 15:40:51'),(108,18,' Approve Leave Request','approve_leave_request',1,0,1,1,'2020-10-05 08:56:27','2025-09-25 15:40:51'),(109,18,'Apply Leave','apply_leave',1,1,0,0,'2019-11-28 23:47:46','2025-09-25 15:40:51'),(110,18,'Leave Types ','leave_types',1,1,1,1,'2018-07-02 10:17:56','2025-09-25 15:40:51'),(111,18,'Department','department',1,1,1,1,'2018-06-26 03:57:07','2025-09-25 15:40:51'),(112,18,'Designation','designation',1,1,1,1,'2018-06-26 03:57:07','2025-09-25 15:40:51'),(113,22,'Fees Collection And Expense Monthly Chart','fees_collection_and_expense_monthly_chart',1,0,0,0,'2018-07-03 07:08:15','2025-09-25 15:40:51'),(114,22,'Fees Collection And Expense Yearly Chart','fees_collection_and_expense_yearly_chart',1,0,0,0,'2018-07-03 07:08:15','2025-09-25 15:40:51'),(115,22,'Monthly Fees Collection Widget','Monthly fees_collection_widget',1,0,0,0,'2018-07-03 07:13:35','2025-09-25 15:40:51'),(116,22,'Monthly Expense Widget','monthly_expense_widget',1,0,0,0,'2018-07-03 07:13:35','2025-09-25 15:40:51'),(117,22,'Student Count Widget','student_count_widget',1,0,0,0,'2018-07-03 07:13:35','2025-09-25 15:40:51'),(118,22,'Staff Role Count Widget','staff_role_count_widget',1,0,0,0,'2018-07-03 07:13:35','2025-09-25 15:40:51'),(122,5,'Attendance By Date','attendance_by_date',1,0,0,0,'2018-07-03 08:42:29','2025-09-25 15:40:51'),(126,15,'User Status','user_status',1,0,0,0,'2018-07-03 08:42:29','2025-09-25 15:40:51'),(127,18,'Can See Other Users Profile','can_see_other_users_profile',1,0,0,0,'2018-07-03 08:42:29','2025-09-25 15:40:51'),(128,1,'Student Timeline','student_timeline',1,1,1,1,'2022-12-28 09:52:24','2025-09-25 15:40:51'),(129,18,'Staff Timeline','staff_timeline',1,1,1,1,'2022-12-28 09:52:24','2025-09-25 15:40:51'),(130,15,'Backup','backup',1,1,0,1,'2018-07-09 04:17:17','2025-09-25 15:40:51'),(131,15,'Restore','restore',1,0,0,0,'2018-07-09 04:17:17','2025-09-25 15:40:51'),(134,1,'Disable Reason','disable_reason',1,1,1,1,'2019-11-27 06:39:21','2025-09-25 15:40:51'),(135,2,'Fees Reminder','fees_reminder',1,0,1,0,'2019-10-25 00:39:49','2025-09-25 15:40:51'),(136,5,'Approve Leave','approve_leave',1,1,1,1,'2022-12-28 09:52:24','2025-09-25 15:40:51'),(137,6,'Exam Group','exam_group',1,1,1,1,'2019-10-25 01:02:34','2025-09-25 15:40:51'),(141,6,'Design Admit Card','design_admit_card',1,1,1,1,'2019-10-25 01:06:59','2025-09-25 15:40:51'),(142,6,'Print Admit Card','print_admit_card',1,0,0,0,'2019-11-23 23:57:51','2025-09-25 15:40:51'),(143,6,'Design Marksheet','design_marksheet',1,1,1,1,'2019-10-25 01:10:25','2025-09-25 15:40:51'),(144,6,'Print Marksheet','print_marksheet',1,0,0,0,'2019-10-25 01:11:02','2025-09-25 15:40:51'),(145,7,'Teachers Timetable','teachers_time_table',1,0,0,0,'2019-11-30 02:52:21','2025-09-25 15:40:51'),(146,14,'Student Report','student_report',1,0,0,0,'2019-10-25 01:27:00','2025-09-25 15:40:51'),(147,14,'Guardian Report','guardian_report',1,0,0,0,'2019-10-25 01:30:27','2025-09-25 15:40:51'),(148,14,'Student History','student_history',1,0,0,0,'2019-10-25 01:39:07','2025-09-25 15:40:51'),(149,14,'Student Login Credential Report','student_login_credential_report',1,0,0,0,'2019-10-25 01:39:07','2025-09-25 15:40:51'),(150,14,'Class Subject Report','class_subject_report',1,0,0,0,'2019-10-25 01:39:07','2025-09-25 15:40:51'),(151,14,'Admission Report','admission_report',1,0,0,0,'2019-10-25 01:39:07','2025-09-25 15:40:51'),(152,14,'Sibling Report','sibling_report',1,0,0,0,'2019-10-25 01:39:07','2025-09-25 15:40:51'),(153,14,'Homework Evaluation Report','homehork_evaluation_report',1,0,0,0,'2019-11-24 01:04:24','2025-09-25 15:40:51'),(154,14,'Student Profile','student_profile',1,0,0,0,'2019-10-25 01:39:07','2025-09-25 15:40:51'),(155,14,'Fees Statement','fees_statement',1,0,0,0,'2019-10-25 01:55:52','2025-09-25 15:40:51'),(156,14,'Balance Fees Report','balance_fees_report',1,0,0,0,'2019-10-25 01:55:52','2025-09-25 15:40:51'),(157,14,'Fees Collection Report','fees_collection_report',1,0,0,0,'2019-10-25 01:55:52','2025-09-25 15:40:51'),(158,14,'Online Fees Collection Report','online_fees_collection_report',1,0,0,0,'2019-10-25 01:55:52','2025-09-25 15:40:51'),(159,14,'Income Report','income_report',1,0,0,0,'2019-10-25 01:55:52','2025-09-25 15:40:51'),(160,14,'Expense Report','expense_report',1,0,0,0,'2019-10-25 01:55:52','2025-09-25 15:40:51'),(161,14,'PayRoll Report','payroll_report',1,0,0,0,'2019-10-31 00:23:22','2025-09-25 15:40:51'),(162,14,'Income Group Report','income_group_report',1,0,0,0,'2019-10-25 01:55:52','2025-09-25 15:40:51'),(163,14,'Expense Group Report','expense_group_report',1,0,0,0,'2019-10-25 01:55:52','2025-09-25 15:40:51'),(164,14,'Attendance Report','attendance_report',1,0,0,0,'2019-10-25 02:08:06','2025-09-25 15:40:51'),(165,14,'Staff Attendance Report','staff_attendance_report',1,0,0,0,'2019-10-25 02:08:06','2025-09-25 15:40:51'),(174,14,'Transport Report','transport_report',1,0,0,0,'2019-10-25 02:13:56','2025-09-25 15:40:51'),(175,14,'Hostel Report','hostel_report',1,0,0,0,'2019-11-27 06:51:53','2025-09-25 15:40:51'),(176,14,'Audit Trail Report','audit_trail_report',1,0,0,0,'2019-10-25 02:16:39','2025-09-25 15:40:51'),(177,14,'User Log','user_log',1,0,0,0,'2019-10-25 02:19:27','2025-09-25 15:40:51'),(178,14,'Book Issue Report','book_issue_report',1,0,0,0,'2019-10-25 02:29:04','2025-09-25 15:40:51'),(179,14,'Book Due Report','book_due_report',1,0,0,0,'2019-10-25 02:29:04','2025-09-25 15:40:51'),(180,14,'Book Inventory Report','book_inventory_report',1,0,0,0,'2019-10-25 02:29:04','2025-09-25 15:40:51'),(181,14,'Stock Report','stock_report',1,0,0,0,'2019-10-25 02:31:28','2025-09-25 15:40:51'),(182,14,'Add Item Report','add_item_report',1,0,0,0,'2019-10-25 02:31:28','2025-09-25 15:40:51'),(183,14,'Issue Item Report','issue_item_report',1,0,0,0,'2019-11-29 03:48:06','2025-09-25 15:40:51'),(185,23,'Online Examination','online_examination',1,1,1,1,'2019-11-23 23:54:50','2025-09-25 15:40:51'),(186,23,'Question Bank','question_bank',1,1,1,1,'2019-11-23 23:55:18','2025-09-25 15:40:51'),(187,6,'Exam Result','exam_result',1,0,0,0,'2019-11-23 23:58:50','2025-09-25 15:40:51'),(188,7,'Subject Group','subject_group',1,1,1,1,'2019-11-24 00:34:32','2025-09-25 15:40:51'),(189,18,'Teachers Rating','teachers_rating',1,0,1,1,'2019-11-24 03:12:54','2025-09-25 15:40:51'),(190,22,'Fees Awaiting Payment Widegts','fees_awaiting_payment_widegts',1,0,0,0,'2019-11-24 00:52:51','2025-09-25 15:40:51'),(191,22,'Converted Leads Widegts','conveted_leads_widegts',1,0,0,0,'2025-02-13 09:03:14','2025-09-25 15:40:51'),(192,22,'Fees Overview Widegts','fees_overview_widegts',1,0,0,0,'2019-11-24 00:57:41','2025-09-25 15:40:51'),(193,22,'Enquiry Overview Widegts','enquiry_overview_widegts',1,0,0,0,'2019-12-02 05:06:09','2025-09-25 15:40:51'),(194,22,'Library Overview Widegts','book_overview_widegts',1,0,0,0,'2019-12-01 01:13:04','2025-09-25 15:40:51'),(195,22,'Student Today Attendance Widegts','today_attendance_widegts',1,0,0,0,'2019-12-03 04:57:45','2025-09-25 15:40:51'),(196,6,'Marks Import','marks_import',1,0,0,0,'2019-11-24 01:02:11','2025-09-25 15:40:51'),(197,14,'Student Attendance Type Report','student_attendance_type_report',1,0,0,0,'2019-11-24 01:06:32','2025-09-25 15:40:51'),(198,14,'Exam Marks Report','exam_marks_report',1,0,0,0,'2019-11-24 01:11:15','2025-09-25 15:40:51'),(200,14,'Online Exam Wise Report','online_exam_wise_report',1,0,0,0,'2019-11-24 01:18:14','2025-09-25 15:40:51'),(201,14,'Online Exams Report','online_exams_report',1,0,0,0,'2019-11-29 02:48:05','2025-09-25 15:40:51'),(202,14,'Online Exams Attempt Report','online_exams_attempt_report',1,0,0,0,'2019-11-29 02:46:24','2025-09-25 15:40:51'),(203,14,'Online Exams Rank Report','online_exams_rank_report',1,0,0,0,'2019-11-24 01:22:25','2025-09-25 15:40:51'),(204,14,'Staff Report','staff_report',1,0,0,0,'2019-11-24 01:25:27','2025-09-25 15:40:51'),(205,6,'Exam','exam',1,1,1,1,'2019-11-24 04:55:48','2025-09-25 15:40:51'),(207,6,'Exam Publish','exam_publish',1,0,0,0,'2019-11-24 05:15:04','2025-09-25 15:40:51'),(208,6,'Link Exam','link_exam',1,0,1,0,'2019-11-24 05:15:04','2025-09-25 15:40:51'),(210,6,'Assign / View student','exam_assign_view_student',1,0,1,0,'2019-11-24 05:15:04','2025-09-25 15:40:51'),(211,6,'Exam Subject','exam_subject',1,0,1,0,'2019-11-24 05:15:04','2025-09-25 15:40:51'),(212,6,'Exam Marks','exam_marks',1,0,1,0,'2019-11-24 05:15:04','2025-09-25 15:40:51'),(213,15,'Language Switcher','language_switcher',1,0,0,0,'2019-11-24 05:17:11','2025-09-25 15:40:51'),(214,23,'Add Questions in Exam ','add_questions_in_exam',1,0,1,0,'2019-11-28 01:38:57','2025-09-25 15:40:51'),(215,15,'Custom Fields','custom_fields',1,0,0,0,'2019-11-29 04:08:35','2025-09-25 15:40:51'),(216,15,'System Fields','system_fields',1,0,0,0,'2019-11-25 00:15:01','2025-09-25 15:40:51'),(217,13,'SMS','sms',1,0,0,0,'2018-06-22 10:40:54','2025-09-25 15:40:51'),(219,14,'Student / Period Attendance Report','student_period_attendance_report',1,0,0,0,'2019-11-29 02:19:31','2025-09-25 15:40:51'),(220,14,'Biometric Attendance Log','biometric_attendance_log',1,0,0,0,'2019-11-27 05:59:16','2025-09-25 15:40:51'),(221,14,'Book Issue Return Report','book_issue_return_report',1,0,0,0,'2019-11-27 06:30:23','2025-09-25 15:40:51'),(222,23,'Assign / View Student','online_assign_view_student',1,0,1,0,'2019-11-28 04:20:22','2025-09-25 15:40:51'),(223,14,'Rank Report','rank_report',1,0,0,0,'2019-11-29 02:30:21','2025-09-25 15:40:51'),(224,25,'Chat','chat',1,0,0,0,'2019-11-29 04:10:28','2025-09-25 15:40:51'),(226,22,'Income Donut Graph','income_donut_graph',1,0,0,0,'2019-11-29 05:00:33','2025-09-25 15:40:51'),(227,22,'Expense Donut Graph','expense_donut_graph',1,0,0,0,'2019-11-29 05:01:10','2025-09-25 15:40:51'),(229,22,'Staff Present Today Widegts','staff_present_today_widegts',1,0,0,0,'2019-11-29 06:48:00','2025-09-25 15:40:51'),(230,22,'Student Present Today Widegts','student_present_today_widegts',1,0,0,0,'2019-11-29 06:47:42','2025-09-25 15:40:51'),(231,26,'Multi Class Student','multi_class_student',1,1,1,1,'2020-10-05 08:56:27','2025-09-25 15:40:51'),(232,27,'Online Admission','online_admission',1,0,1,1,'2019-12-02 06:11:10','2025-09-25 15:40:51'),(233,15,'print_header_footer','print_header_footer',1,1,1,1,'2020-02-12 02:02:02','2026-02-02 09:03:17'),(234,28,'Manage Alumni','manage_alumni',1,1,1,1,'2020-06-02 03:15:46','2025-09-25 15:40:51'),(235,28,'Events','events',1,1,1,1,'2020-05-28 21:48:52','2025-09-25 15:40:51'),(236,29,'Manage Lesson Plan','manage_lesson_plan',1,1,1,0,'2020-05-28 22:17:37','2025-09-25 15:40:51'),(237,29,'Manage Syllabus Status','manage_syllabus_status',1,0,1,0,'2020-05-28 22:20:11','2025-09-25 15:40:51'),(238,29,'Lesson','lesson',1,1,1,1,'2020-05-28 22:20:11','2025-09-25 15:40:51'),(239,29,'Topic','topic',1,1,1,1,'2020-05-28 22:20:11','2025-09-25 15:40:51'),(240,14,'Syllabus Status Report','syllabus_status_report',1,0,0,0,'2020-05-28 23:17:54','2025-09-25 15:40:51'),(241,14,'Teacher Syllabus Status Report','teacher_syllabus_status_report',1,0,0,0,'2020-05-28 23:17:54','2025-09-25 15:40:51'),(242,14,'Alumni Report','alumni_report',1,0,0,0,'2020-06-07 23:59:54','2025-09-25 15:40:51'),(243,15,'Student Profile Update','student_profile_update',1,0,0,0,'2020-08-21 05:36:33','2025-09-25 15:40:51'),(244,14,'Student Gender Ratio Report','student_gender_ratio_report',1,0,0,0,'2020-08-22 12:37:51','2025-09-25 15:40:51'),(245,14,'Student Teacher Ratio Report','student_teacher_ratio_report',1,0,0,0,'2020-08-22 12:42:27','2025-09-25 15:40:51'),(246,14,'Daily Attendance Report','daily_attendance_report',1,0,0,0,'2020-08-22 12:43:16','2025-09-25 15:40:51'),(247,23,'Import Question','import_question',1,0,0,0,'2019-11-23 18:25:18','2025-09-25 15:40:51'),(248,20,'Staff ID Card','staff_id_card',1,1,1,1,'2018-07-06 10:41:28','2025-09-25 15:40:51'),(249,20,'Generate Staff ID Card','generate_staff_id_card',1,0,0,0,'2018-07-06 10:41:49','2025-09-25 15:40:51'),(250,19,'Daily Assignment','daily_assignment',1,0,0,0,'2022-03-02 07:28:23','2025-09-25 15:40:51'),(251,6,'Marks Division','marks_division',1,1,1,1,'2022-07-01 15:24:16','2025-09-25 15:40:51'),(252,13,'Schedule Email SMS Log','schedule_email_sms_log',1,0,1,0,'2022-07-09 11:25:16','2025-09-25 15:40:51'),(253,13,'Login Credentials Send','login_credentials_send',1,0,0,0,'2022-07-01 15:46:10','2025-09-25 15:40:51'),(254,13,'Email Template','email_template',1,1,1,1,'2022-07-01 15:46:10','2025-09-25 15:40:51'),(255,13,'SMS Template','sms_template',1,1,1,1,'2022-07-01 15:46:10','2025-09-25 15:40:51'),(256,14,'Balance Fees Report With Remark','balance_fees_report_with_remark',1,0,0,0,'2019-10-25 01:55:52','2025-09-25 15:40:51'),(257,14,'Balance Fees Statement','balance_fees_statement',1,0,0,0,'2019-10-25 01:55:52','2025-09-25 15:40:51'),(258,14,'Daily Collection Report','daily_collection_report',1,0,0,0,'2019-10-25 01:55:52','2025-09-25 15:40:51'),(259,11,'Fees Master','transport_fees_master',1,0,1,0,'2022-07-05 09:29:19','2025-09-25 15:40:51'),(260,11,'Pickup Point','pickup_point',1,1,1,1,'2022-07-04 09:50:08','2025-09-25 15:40:51'),(261,11,'Route Pickup Point','route_pickup_point',1,1,1,1,'2022-07-04 09:50:08','2025-09-25 15:40:51'),(262,11,'Student Transport Fees','student_transport_fees',1,1,1,0,'2022-07-05 10:15:55','2025-09-25 15:40:51'),(263,29,'Comments','lesson_plan_comments',1,1,0,1,'2020-05-28 22:20:11','2025-09-25 15:40:51'),(264,15,'Sidebar Menu','sidebar_menu',1,0,0,0,'2022-07-11 12:01:17','2025-09-25 15:40:51'),(265,15,'Currency','currency',1,0,0,0,'2020-08-21 05:36:33','2025-09-25 15:40:51'),(266,6,'Exam Schedule','exam_schedule',1,0,0,0,'2019-11-23 23:58:50','2025-09-25 15:40:51'),(267,6,'Generate Rank','generate_rank',1,0,0,0,'2019-11-24 05:15:04','2025-09-25 15:40:51'),(268,8,'Content Type','content_type',1,1,1,1,'2022-07-08 05:18:54','2025-09-25 15:40:51'),(269,8,'Content Share List','content_share_list',1,0,0,1,'2022-07-08 05:18:58','2025-09-25 15:40:51'),(270,8,'Video Tutorial','video_tutorial',1,1,1,1,'2022-07-08 05:19:01','2025-09-25 15:40:51'),(271,15,'Currency Switcher','currency_switcher',1,0,0,0,'2019-11-24 05:17:11','2025-09-25 15:40:51'),(272,2,'Offline Bank Payments','offline_bank_payments',1,0,0,0,'2018-06-27 00:18:15','2025-09-25 15:40:51'),(273,29,'Copy Old Lessons','copy_old_lesson',1,0,0,0,'2020-05-28 22:20:11','2025-09-25 15:40:51'),(274,30,'Annual Calendar','annual_calendar',1,1,1,1,'2020-05-28 22:20:11','2025-09-25 15:40:51'),(275,30,'Holiday Type','holiday_type',1,1,1,1,'2024-10-14 12:31:14','2025-09-25 15:40:51'),(276,14,'Online Admission Report','online_admission_report',1,0,0,0,'2020-08-22 12:42:27','2025-09-25 15:40:51'),(277,31,'Download CV','download_cv',1,0,0,0,'2024-12-10 11:06:30','2025-09-25 15:40:51'),(278,31,'Build CV','build_cv',1,1,0,1,'2024-12-13 07:05:10','2025-09-25 15:40:51'),(279,31,'Setting','download_cv_setting',1,0,0,0,'2024-12-10 11:06:30','2025-09-25 15:40:51'),(280,22,'Student Head Count Widget','student_head_count_widget',1,0,0,0,'2018-07-03 07:13:35','2025-09-25 15:40:51'),(281,22,'Staff Approved Leave Widegts','staff_approved_leave_widegts',1,0,0,0,'2018-07-03 07:13:35','2025-09-25 15:40:51'),(282,22,'Student Approved Leave Widegts','student_approved_leave_widegts',1,0,0,0,'2018-07-03 07:13:35','2025-09-25 15:40:51'),(283,2,'Incidental Fee Type','incidental_fee_type',1,1,1,1,'2025-11-03 03:47:56','2026-02-02 07:07:35'),(284,2,'Assign Incidental Fee','assign_incidental_fee',1,1,1,1,'2025-11-03 03:47:56','2026-02-02 07:07:35'),(285,2,'Collect Incidental Fee','collect_incidental_fee',1,1,1,1,'2025-11-03 03:47:56','2026-02-02 07:07:35'),(286,2,'Incidental Fee Report','incidental_fee_report',1,1,1,1,'2025-11-03 03:47:56','2026-02-02 07:07:35'),(5001,500,'Setting','setting',1,0,1,0,'2020-06-10 13:39:04','2026-03-06 19:05:29'),(5002,500,'Live Classes','live_classes',1,1,0,1,'2020-05-31 15:41:32','2026-03-06 19:05:29'),(5003,500,'Live Meeting','live_meeting',1,1,0,1,'2020-06-01 12:41:41','2026-03-06 19:05:29'),(5004,500,'Live Meeting Report','live_meeting_report',1,0,0,0,'2020-06-10 05:07:40','2026-03-06 19:05:29'),(5005,500,'Live Classes Report','live_classes_report',1,0,0,0,'2020-06-10 06:29:53','2026-03-06 19:05:29'),(9001,900,'CBSE Exam','cbse_exam',1,1,1,1,'2022-11-03 08:58:30','2026-03-05 21:29:42'),(9002,900,'CBSE Exam Schedule','cbse_exam_schedule',1,0,0,0,'2023-05-09 11:01:34','2026-03-05 21:29:42'),(9003,900,'CBSE Exam Assign / View Student','cbse_exam_assign_view_student',1,0,1,0,'2022-11-03 09:18:15','2026-03-05 21:29:42'),(9004,900,'CBSE Exam Subjects','cbse_exam_subjects',1,0,1,0,'2022-11-04 08:01:41','2026-03-05 21:29:42'),(9005,900,'CBSE Exam Marks','cbse_exam_marks',1,0,1,0,'2022-11-03 09:18:24','2026-03-05 21:29:42'),(9006,900,'CBSE Exam Attendance','cbse_exam_attendance',1,0,1,0,'2022-11-03 09:18:28','2026-03-05 21:29:42'),(9007,900,'CBSE Exam Teacher Remark','cbse_exam_teacher_remark',1,0,1,0,'2022-11-03 09:18:32','2026-03-05 21:29:42'),(9008,900,'CBSE Exam Print Marksheet','cbse_exam_print_marksheet',1,0,0,0,'2022-11-03 09:18:43','2026-03-05 21:29:42'),(9009,900,'CBSE Exam Grade','cbse_exam_grade',1,1,1,1,'2022-11-03 09:18:46','2026-03-05 21:29:42'),(9010,900,'CBSE Exam Assign Observation','cbse_exam_assign_observation',1,1,1,1,'2023-05-08 12:33:23','2026-03-05 21:29:42'),(9011,900,'CBSE Exam Observation','cbse_exam_observation',1,1,1,1,'2023-05-09 10:57:16','2026-03-05 21:29:42'),(9012,900,'CBSE Exam Observation Parameter','cbse_exam_observation_parameter',1,1,1,1,'2023-05-09 11:01:54','2026-03-05 21:29:42'),(9013,900,'CBSE Exam Assessment','cbse_exam_assessment',1,1,1,1,'2023-05-09 11:01:51','2026-03-05 21:29:42'),(9014,900,'CBSE Exam Term','cbse_exam_term',1,1,1,1,'2023-05-09 11:01:47','2026-03-05 21:29:42'),(9015,900,'CBSE Exam Template','cbse_exam_template',1,1,1,1,'2023-05-09 11:01:43','2026-03-05 21:29:42'),(9016,900,'CBSE Exam Link Exam','cbse_exam_link_exam',1,0,0,0,'2023-05-09 11:01:40','2026-03-05 21:29:42'),(9017,900,'CBSE Exam Subject Marks Report','subject_marks_report',1,0,0,0,'2023-05-09 11:01:38','2026-03-05 21:29:42'),(9018,900,'CBSE Exam Template Marks Report','template_marks_report',1,0,0,0,'2023-05-09 11:01:34','2026-03-05 21:29:42'),(9019,900,'CBSE Exam Setting','cbse_exam_setting',1,0,0,0,'2023-07-03 05:24:57','2026-03-05 21:29:42'),(9020,900,'CBSE Exam Generate Rank','cbse_exam_generate_rank',1,0,0,0,'2023-07-03 05:24:57','2026-03-05 21:29:42'),(9021,900,'CBSE Exam Design Admit Card','cbse_exam_admit_card',1,1,1,1,'2023-07-03 03:24:57','2025-10-04 06:41:15'),(9022,900,'CBSE Exam Print Admit Card','cbse_exam_print_admit_card',1,0,0,0,'2023-07-03 03:24:57','2025-10-04 06:41:15'),(9023,900,'CBSE Exam Category','cbse_exam_category',1,1,1,1,'2023-07-03 03:24:57','2025-10-04 06:41:15'),(10001,1000,'Overview','multi_branch_overview',1,0,0,0,'2022-11-14 23:37:36','2025-11-08 01:23:13'),(10002,1000,'Daily Collection Report','multi_branch_daily_collection_report',1,0,0,0,'2022-11-14 23:27:02','2025-11-08 01:23:13'),(10003,1000,'Payroll Report','multi_branch_payroll',1,0,0,0,'2022-11-16 05:49:48','2025-11-08 01:23:13'),(10004,1000,'Income Report','multi_branch_income_report',1,0,0,0,'2022-11-14 23:37:36','2025-11-08 01:23:13'),(10005,1000,'Expense Report','multi_branch_expense_report',1,0,0,0,'2022-11-14 23:32:27','2025-11-08 01:23:13'),(10006,1000,'User Log Report','multi_branch_user_log_report',1,0,0,0,'2022-11-14 23:32:27','2025-11-08 01:23:13'),(10007,1000,'Setting','multi_branch_setting',1,0,0,0,'2022-11-14 23:37:36','2025-11-08 01:23:13'),(10008,9,'Book List','book_list',1,0,1,1,'2026-01-21 09:37:50','2026-03-16 08:50:24'),(10009,9,'Issue - Return','issue_return',1,1,0,0,'2026-01-21 09:37:50','2026-03-16 08:50:24'),(10010,9,'Add Student Member','add_student',1,1,0,0,'2026-01-21 09:37:50','2026-03-16 08:50:24'),(10011,9,'Add Staff Member','add_staff_member',1,1,0,0,'2026-01-21 09:37:50','2026-03-16 08:50:24'),(10012,9,'Category Master','library_category',1,1,1,1,'2026-01-21 09:37:50','2026-03-16 08:50:24'),(10013,9,'Sub Category Master','library_subcategory',1,1,1,1,'2026-01-21 09:37:50','2026-03-16 08:50:24'),(10014,9,'Publisher Master','library_publisher',1,1,1,1,'2026-01-21 09:37:50','2026-03-16 08:50:24'),(10015,9,'Vendor Master','library_vendor',1,1,1,1,'2026-01-21 09:37:50','2026-03-16 08:50:24'),(10016,9,'Book Type Master','library_book_type',1,1,1,1,'2026-01-21 09:37:50','2026-03-16 08:50:24'),(10017,9,'Subject Master','library_subject',1,1,1,1,'2026-01-21 09:37:50','2026-03-16 08:50:24'),(10018,9,'Position Rack Master','library_position_rack',1,1,1,1,'2026-01-21 09:37:50','2026-03-16 08:50:24'),(10019,9,'Position Shelf Master','library_position_shelf',1,1,1,1,'2026-01-21 09:37:50','2026-03-16 08:50:24'),(10020,9,'OPAC','library_opac',1,0,0,0,'2026-01-21 09:37:50','2026-03-16 08:50:24'),(10021,9,'Check In - Check Out','library_checkin_checkout',1,1,0,0,'2026-01-21 09:37:50','2026-03-16 08:50:24'),(10022,9,'Checkout Pendings','library_checkout_pending',1,0,0,0,'2026-01-21 09:37:50','2026-03-16 08:50:24'),(10023,27,'Online Admission Manual Payment','online_admission_manual_payment',0,1,0,0,'2026-01-28 07:41:18','2026-01-28 17:03:58'),(10024,0,'Online Admission Admission Courses','online_admission_admission_courses',1,1,1,1,'2026-01-28 10:23:46','2026-01-28 10:23:46'),(10025,18,'Special Attendance',NULL,1,1,1,1,'2026-02-04 15:18:19','2026-02-04 15:18:19'),(10026,18,'Initial Leave Balance','initial_leave_balance',1,0,1,0,'2026-02-10 18:20:47','2026-02-10 18:20:47'),(10027,18,'Attendance Exceptions','attendance_exceptions',1,0,1,0,'2026-02-10 18:48:06','2026-02-10 18:48:06'),(10028,15,'Set Final Years','final_year_classes',1,1,1,0,'2026-02-16 17:20:06','2026-02-16 17:20:06'),(10029,22,'Classwise Fees Summary Widget','fees_classwise_summary_widget',1,0,0,0,'2026-02-16 17:34:21','2026-02-16 17:34:21'),(15001,1500,'Whatsapp Messaging','whatsapp_messaging',1,0,0,0,'2024-12-18 07:12:35','2025-10-04 06:41:15'),(15002,10,'Inventory Dashboard','inventory_dashboard',1,0,0,0,'2026-03-16 06:47:53','2026-03-16 06:47:53'),(15003,10,'Inventory Indents','inventory_indents',1,1,1,0,'2026-03-16 06:47:53','2026-03-16 06:47:53'),(15004,10,'Indent Approvals','indent_approvals',1,0,1,0,'2026-03-16 06:47:53','2026-03-16 06:47:53'),(15005,10,'Purchase Orders','purchase_orders',1,1,1,0,'2026-03-16 06:47:53','2026-03-16 06:47:53'),(15006,10,'PO Approvals','po_approvals',1,0,1,0,'2026-03-16 06:47:53','2026-03-16 06:47:53'),(15007,10,'Goods Receipts','goods_receipts',1,1,0,0,'2026-03-16 06:47:53','2026-03-16 06:47:53'),(15008,10,'Asset Register','asset_register',1,0,0,0,'2026-03-16 06:47:53','2026-03-16 06:47:53'),(15009,10,'Asset Assignment','asset_assignment',1,1,1,0,'2026-03-16 06:47:53','2026-03-16 06:47:53'),(15010,10,'Asset Transfer','asset_transfer',1,1,0,0,'2026-03-16 06:47:53','2026-03-16 06:47:53'),(15011,10,'Asset Maintenance','asset_maintenance',1,1,1,0,'2026-03-16 06:47:53','2026-03-16 06:47:53'),(15012,NULL,'update_leave_balance',NULL,0,0,0,0,'2026-04-05 10:22:12','2026-04-05 10:22:12');
/*!40000 ALTER TABLE `permission_category` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `permission_group`
--

LOCK TABLES `permission_group` WRITE;
/*!40000 ALTER TABLE `permission_group` DISABLE KEYS */;
INSERT INTO `permission_group` VALUES (1,'Student Information','student_information',1,1,'2019-03-15 09:30:22','2025-09-25 15:40:51'),(2,'Fees Collection','fees_collection',1,0,'2020-06-11 00:51:35','2025-09-25 15:40:51'),(3,'Income','income',1,0,'2020-06-01 01:57:39','2025-09-25 15:40:51'),(4,'Expense','expense',1,0,'2019-03-15 09:06:22','2025-09-25 15:40:51'),(5,'Student Attendance','student_attendance',1,0,'2018-07-02 07:48:08','2025-09-25 15:40:51'),(6,'Examination','examination',1,0,'2018-07-11 02:49:08','2025-09-25 15:40:51'),(7,'Academics','academics',1,1,'2018-07-02 07:25:43','2025-09-25 15:40:51'),(8,'Download Center','download_center',1,0,'2018-07-02 07:49:29','2025-09-25 15:40:51'),(9,'Library','library',1,0,'2018-06-28 11:13:14','2025-09-25 15:40:51'),(10,'Inventory','inventory',1,0,'2018-06-27 00:48:58','2025-09-25 15:40:51'),(11,'Transport','transport',1,0,'2018-06-27 07:51:26','2025-09-25 15:40:51'),(12,'Hostel','hostel',1,0,'2018-07-02 07:49:32','2025-09-25 15:40:51'),(13,'Communicate','communicate',1,0,'2018-07-02 07:50:00','2025-09-25 15:40:51'),(14,'Reports','reports',1,1,'2018-06-27 03:40:22','2025-09-25 15:40:51'),(15,'System Settings','system_settings',1,1,'2018-06-27 03:40:28','2025-09-25 15:40:51'),(16,'Front CMS','front_cms',1,0,'2018-07-10 05:16:54','2025-09-25 15:40:51'),(17,'Front Office','front_office',1,0,'2018-06-27 03:45:30','2025-09-25 15:40:51'),(18,'Human Resource','human_resource',1,1,'2018-06-27 03:41:02','2025-09-25 15:40:51'),(19,'Homework','homework',1,0,'2018-06-27 00:49:38','2025-09-25 15:40:51'),(20,'Certificate','certificate',1,0,'2018-06-27 07:51:29','2025-09-25 15:40:51'),(21,'Calendar To Do List','calendar_to_do_list',1,0,'2019-03-15 09:06:25','2025-09-25 15:40:51'),(22,'Dashboard and Widgets','dashboard_and_widgets',1,1,'2018-06-27 03:41:17','2025-09-25 15:40:51'),(23,'Online Examination','online_examination',1,0,'2020-06-01 02:25:36','2025-09-25 15:40:51'),(25,'Chat','chat',1,0,'2019-11-23 23:54:04','2025-09-25 15:40:51'),(26,'Multi Class','multi_class',1,0,'2019-11-27 12:14:14','2025-09-25 15:40:51'),(27,'Online Admission','online_admission',1,0,'2019-11-27 02:42:13','2025-09-25 15:40:51'),(28,'Alumni','alumni',1,0,'2020-05-29 00:26:38','2025-09-25 15:40:51'),(29,'Lesson Plan','lesson_plan',1,0,'2020-06-07 05:38:30','2025-09-25 15:40:51'),(30,'Annual Calendar','annual_calendar',1,0,'2024-10-22 10:45:56','2025-09-25 15:40:51'),(31,'Student CV','student_cv',1,0,'2024-12-13 11:54:57','2025-09-25 15:40:51'),(500,'Zoom Live Classes','zoom_live_classes',1,0,'2020-06-10 13:37:23','2026-03-06 19:05:29'),(900,'CBSE Examination','cbseexam',1,0,'2023-05-25 12:04:56','2026-03-05 21:29:42'),(1000,'Multi Branch','multi_branch',1,0,'2022-11-17 05:23:36','2025-11-08 01:23:13'),(1500,'Whatsapp Messaging','whatsapp_messaging',1,0,'2025-01-10 04:36:34','2025-10-04 06:41:15');
/*!40000 ALTER TABLE `permission_group` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `print_headerfooter`
--

LOCK TABLES `print_headerfooter` WRITE;
/*!40000 ALTER TABLE `print_headerfooter` DISABLE KEYS */;
INSERT INTO `print_headerfooter` VALUES (1,'staff_payslip','','This payslip is computer generated hence no signature is required.',1,'2026-04-12 07:15:20','2022-12-28 09:52:24','2026-04-12 07:15:20'),(2,'student_receipt','','This receipt is computer generated hence no signature is required.',1,'2026-04-12 07:15:20','2022-12-28 09:52:24','2026-04-12 07:15:20'),(3,'online_admission_receipt','','This receipt is for online admission, &nbsp;computer generated hence no signature is required.',1,'2026-04-12 07:15:20','2022-12-28 09:52:24','2026-04-12 07:15:20'),(4,'online_exam','','This receipt is for online exam computer  generated hence no signature is required.',1,'2026-04-12 07:15:20','2022-09-08 17:28:34','2026-04-12 07:15:20'),(5,'general_purpose','','<h1>\r\n\r\n</h1><p>footer text here ....</p>',1,'2026-04-12 07:15:20','2022-09-08 17:28:34','2026-04-12 07:15:20');
/*!40000 ALTER TABLE `print_headerfooter` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `roles`
--

LOCK TABLES `roles` WRITE;
/*!40000 ALTER TABLE `roles` DISABLE KEYS */;
INSERT INTO `roles` VALUES (1,'Admin',NULL,0,1,0,'2018-06-30 15:39:11','0000-00-00 00:00:00'),(2,'Teacher',NULL,0,1,0,'2018-06-30 15:39:14','0000-00-00 00:00:00'),(3,'Accountant',NULL,0,1,0,'2018-06-30 15:39:17','0000-00-00 00:00:00'),(4,'Librarian',NULL,0,1,0,'2018-06-30 15:39:21','0000-00-00 00:00:00'),(6,'Receptionist',NULL,0,1,0,'2018-07-02 05:39:03','0000-00-00 00:00:00'),(7,'Super Admin',NULL,0,1,1,'2018-07-11 14:11:29','0000-00-00 00:00:00'),(8,'Admission Wing',NULL,0,0,0,'2026-01-24 11:27:18','2026-01-24 11:27:18'),(9,'Chairman',NULL,0,0,0,'2026-02-16 17:20:06','2026-02-16 17:20:06'),(10,'Principal',NULL,0,0,0,'2026-02-16 17:20:06','2026-02-16 17:20:06'),(11,'Head Office',NULL,0,0,0,'2026-02-16 17:20:06','2026-02-16 17:20:06'),(12,'Office Assitant',NULL,0,0,0,'2026-02-27 12:15:48','2026-02-27 12:15:48'),(13,'Others',NULL,0,0,0,'2026-02-27 12:21:16','2026-02-27 12:21:16'),(14,'Trust Office',NULL,0,0,0,'2026-03-13 09:14:33','2026-03-13 09:14:33');
/*!40000 ALTER TABLE `roles` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `roles_permissions`
--

LOCK TABLES `roles_permissions` WRITE;
/*!40000 ALTER TABLE `roles_permissions` DISABLE KEYS */;
INSERT INTO `roles_permissions` VALUES (11,1,78,1,1,1,1,'2018-07-03 00:49:43','2025-09-25 15:40:51'),(23,1,12,1,1,1,1,'2018-07-06 09:45:38','2025-09-25 15:40:51'),(24,1,13,1,1,1,1,'2018-07-06 09:48:28','2025-09-25 15:40:51'),(26,1,15,1,1,1,0,'2019-11-27 23:47:28','2025-09-25 15:40:51'),(31,1,21,1,0,0,0,'2019-11-26 04:51:15','2026-03-13 11:08:57'),(34,1,24,1,0,0,0,'2019-11-28 06:35:20','2026-03-13 11:09:10'),(43,1,32,1,1,1,1,'2018-07-06 10:22:05','2025-09-25 15:40:51'),(44,1,33,1,1,1,1,'2018-07-06 10:22:29','2025-09-25 15:40:51'),(45,1,34,1,1,1,1,'2018-07-06 10:23:59','2025-09-25 15:40:51'),(46,1,35,1,1,1,1,'2018-07-06 10:24:34','2025-09-25 15:40:51'),(47,1,104,1,1,1,1,'2018-07-06 10:23:08','2025-09-25 15:40:51'),(48,1,37,1,1,1,1,'2018-07-06 10:25:30','2026-03-13 11:11:06'),(49,1,38,1,1,1,1,'2018-07-09 05:15:27','2025-09-25 15:40:51'),(61,1,55,1,1,0,0,'2018-07-02 09:24:16','2026-03-02 04:55:20'),(67,1,61,1,1,0,0,'2018-07-09 05:59:19','2026-03-02 04:55:36'),(68,1,62,1,1,0,0,'2018-07-09 05:59:19','2026-03-02 04:55:36'),(69,1,63,1,1,0,0,'2018-07-09 03:51:38','2026-03-02 04:55:40'),(70,1,64,1,1,1,0,'2018-07-09 03:02:19','2026-03-02 04:55:40'),(71,1,65,1,1,1,0,'2018-07-09 03:11:21','2026-03-02 04:55:41'),(72,1,66,1,1,1,0,'2018-07-09 03:13:09','2026-03-02 04:55:42'),(73,1,67,1,1,1,0,'2018-07-09 03:14:47','2026-03-02 04:55:43'),(74,1,79,1,1,0,1,'2019-11-30 01:32:51','2025-09-25 15:40:51'),(75,1,80,1,1,1,1,'2018-07-06 09:41:23','2025-09-25 15:40:51'),(76,1,81,1,1,1,1,'2018-07-06 09:41:23','2025-09-25 15:40:51'),(78,1,83,1,1,1,1,'2018-07-06 09:41:23','2025-09-25 15:40:51'),(79,1,84,1,1,1,1,'2018-07-06 09:41:23','2025-09-25 15:40:51'),(80,1,85,1,1,1,1,'2018-07-12 00:16:00','2025-09-25 15:40:51'),(94,1,82,1,1,1,1,'2018-07-06 09:41:23','2025-09-25 15:40:51'),(120,1,39,1,1,1,1,'2018-07-06 10:26:28','2025-09-25 15:40:51'),(156,1,9,1,1,1,1,'2019-11-27 23:45:46','2025-09-25 15:40:51'),(157,1,10,1,1,1,1,'2019-11-27 23:45:46','2025-09-25 15:40:51'),(159,1,40,1,1,1,1,'2019-11-30 00:49:39','2025-09-25 15:40:51'),(160,1,41,1,1,1,1,'2019-12-02 05:43:41','2025-09-25 15:40:51'),(161,1,42,1,1,1,1,'2019-11-30 00:49:39','2025-09-25 15:40:51'),(169,1,27,1,1,0,1,'2019-11-29 06:15:37','2025-09-25 15:40:51'),(178,1,54,1,0,0,0,'2018-07-05 09:09:22','2026-03-02 04:55:19'),(179,1,56,1,0,0,0,'2019-11-30 00:49:54','2026-03-02 04:55:21'),(180,1,57,1,0,0,0,'2019-11-30 01:32:51','2026-03-02 04:55:22'),(181,1,58,1,0,0,0,'2019-11-30 01:32:51','2026-03-02 04:55:23'),(182,1,59,1,0,0,0,'2019-11-30 01:32:51','2026-03-02 04:55:24'),(183,1,60,1,0,0,0,'2019-11-30 00:59:57','2026-03-02 04:55:26'),(201,1,14,1,0,0,0,'2018-07-02 11:22:03','2025-09-25 15:40:51'),(204,1,26,1,0,0,0,'2018-07-02 11:32:05','2025-09-25 15:40:51'),(206,1,29,1,0,0,0,'2018-07-02 11:43:54','2025-09-25 15:40:51'),(207,1,30,1,0,0,0,'2018-07-02 11:43:54','2025-09-25 15:40:51'),(208,1,31,1,1,1,1,'2019-11-30 01:32:51','2025-09-25 15:40:51'),(222,1,1,1,0,0,0,'2019-11-27 22:55:06','2026-03-13 11:09:53'),(307,1,126,1,0,0,0,'2018-07-03 09:26:13','2025-09-25 15:40:51'),(315,1,123,1,0,0,0,'2018-07-03 10:27:03','2025-09-25 15:40:51'),(369,1,102,1,1,1,1,'2019-12-02 05:02:15','2025-09-25 15:40:51'),(435,1,96,1,1,1,1,'2018-07-09 01:03:54','2025-09-25 15:40:51'),(461,1,97,1,0,0,0,'2018-07-09 01:00:16','2025-09-25 15:40:51'),(464,1,86,1,1,1,0,'2019-11-28 06:39:19','2026-04-02 06:24:42'),(474,1,130,1,1,0,0,'2018-07-09 10:56:36','2026-03-02 04:55:10'),(476,1,131,1,0,0,0,'2018-07-09 04:53:32','2025-09-25 15:40:51'),(557,6,82,1,1,1,1,'2019-12-01 01:48:28','2025-09-25 15:40:51'),(558,6,83,1,1,1,1,'2019-12-01 01:49:08','2025-09-25 15:40:51'),(559,6,84,1,1,1,1,'2019-12-01 01:49:59','2025-09-25 15:40:51'),(575,6,44,1,0,0,0,'2018-07-10 07:35:33','2025-09-25 15:40:51'),(576,6,46,1,0,0,0,'2018-07-10 07:35:33','2025-09-25 15:40:51'),(578,6,102,1,1,1,1,'2019-12-01 01:52:27','2025-09-25 15:40:51'),(625,1,28,1,1,1,1,'2019-11-29 06:19:18','2025-09-25 15:40:51'),(634,4,102,1,1,1,1,'2019-12-01 01:03:00','2025-09-25 15:40:51'),(669,1,145,1,0,0,0,'2019-11-26 04:51:15','2025-09-25 15:40:51'),(677,1,153,1,0,0,0,'2019-11-01 02:28:24','2025-09-25 15:40:51'),(720,1,216,1,0,0,0,'2019-11-26 05:24:12','2025-09-25 15:40:51'),(728,1,185,1,1,1,1,'2019-11-28 02:50:33','2025-09-25 15:40:51'),(729,1,186,1,1,1,1,'2019-11-28 02:49:07','2025-09-25 15:40:51'),(730,1,214,1,0,1,0,'2019-11-28 01:47:53','2025-09-25 15:40:51'),(732,1,198,1,0,0,0,'2019-11-26 05:24:30','2025-09-25 15:40:51'),(734,1,200,1,0,0,0,'2019-11-26 05:24:30','2025-09-25 15:40:51'),(735,1,201,1,0,0,0,'2019-11-26 05:24:30','2025-09-25 15:40:51'),(736,1,202,1,0,0,0,'2019-11-26 05:24:30','2025-09-25 15:40:51'),(737,1,203,1,0,0,0,'2019-11-26 05:24:30','2025-09-25 15:40:51'),(747,1,2,1,0,0,0,'2019-11-27 22:56:08','2025-09-25 15:40:51'),(748,1,3,1,0,0,0,'2019-11-27 22:56:32','2026-03-13 11:10:00'),(749,1,4,1,0,0,0,'2019-11-27 22:56:48','2026-03-13 11:10:05'),(751,1,128,1,0,0,0,'2019-11-27 22:57:01','2026-03-13 11:10:08'),(754,1,134,1,0,0,0,'2019-11-27 23:18:21','2026-03-13 11:10:18'),(755,1,5,1,1,0,1,'2019-11-27 23:35:07','2025-09-25 15:40:51'),(756,1,6,1,0,0,0,'2019-11-27 23:35:25','2025-09-25 15:40:51'),(757,1,7,1,1,1,1,'2019-11-27 23:36:35','2025-09-25 15:40:51'),(758,1,8,1,1,1,1,'2019-11-27 23:37:27','2025-09-25 15:40:51'),(760,1,68,1,0,0,0,'2019-11-27 23:38:06','2025-09-25 15:40:51'),(761,1,69,1,1,1,1,'2019-11-27 23:39:06','2025-09-25 15:40:51'),(762,1,70,1,1,1,1,'2019-11-27 23:39:41','2025-09-25 15:40:51'),(763,1,71,1,0,0,0,'2019-11-27 23:39:59','2025-09-25 15:40:51'),(765,1,73,1,0,0,0,'2019-11-27 23:43:15','2025-09-25 15:40:51'),(766,1,74,1,0,0,0,'2019-11-27 23:43:55','2025-09-25 15:40:51'),(768,1,11,1,0,0,0,'2019-11-27 23:45:46','2025-09-25 15:40:51'),(769,1,122,1,0,0,0,'2019-11-27 23:52:43','2025-09-25 15:40:51'),(771,1,136,1,0,0,0,'2019-11-27 23:55:36','2025-09-25 15:40:51'),(772,1,20,1,1,1,1,'2019-11-28 04:06:44','2025-09-25 15:40:51'),(773,1,137,1,1,1,1,'2019-11-28 00:46:14','2025-09-25 15:40:51'),(774,1,141,1,1,1,1,'2019-11-28 00:59:42','2025-09-25 15:40:51'),(775,1,142,1,0,0,0,'2019-11-27 23:56:12','2025-09-25 15:40:51'),(776,1,143,1,1,1,1,'2019-11-28 00:59:42','2025-09-25 15:40:51'),(777,1,144,1,0,0,0,'2019-11-27 23:56:12','2025-09-25 15:40:51'),(778,1,187,1,0,0,0,'2019-11-27 23:56:12','2025-09-25 15:40:51'),(779,1,196,1,0,0,0,'2019-11-27 23:56:12','2025-09-25 15:40:51'),(781,1,207,1,0,0,0,'2019-11-27 23:56:12','2025-09-25 15:40:51'),(782,1,208,1,0,1,0,'2019-11-28 00:10:22','2025-09-25 15:40:51'),(783,1,210,1,0,1,0,'2019-11-28 00:34:40','2025-09-25 15:40:51'),(784,1,211,1,0,1,0,'2019-11-28 00:38:23','2025-09-25 15:40:51'),(785,1,212,1,0,1,0,'2019-11-28 00:42:15','2025-09-25 15:40:51'),(786,1,205,1,1,1,1,'2019-11-28 00:42:15','2025-09-25 15:40:51'),(787,1,222,1,0,1,0,'2019-11-28 01:36:36','2025-09-25 15:40:51'),(788,1,77,1,0,0,0,'2019-11-28 06:22:10','2026-03-13 11:09:20'),(789,1,188,1,0,0,0,'2019-11-28 06:26:16','2026-03-13 11:09:23'),(790,1,23,1,0,0,0,'2019-11-28 06:34:20','2026-03-13 11:09:07'),(791,1,25,1,0,0,0,'2019-11-28 06:36:20','2026-03-13 11:09:15'),(792,1,127,1,0,0,0,'2019-11-28 06:41:25','2025-09-25 15:40:51'),(794,1,88,1,1,1,0,'2019-11-28 06:43:04','2025-09-25 15:40:51'),(795,1,90,1,1,0,0,'2019-11-28 06:46:22','2026-03-13 11:08:26'),(796,1,108,1,0,1,0,'2021-01-23 07:09:32','2026-03-27 07:15:33'),(797,1,109,1,1,0,0,'2019-11-28 23:38:11','2026-03-16 06:45:03'),(798,1,110,1,1,1,1,'2019-11-28 23:49:29','2025-09-25 15:40:51'),(799,1,111,1,1,1,1,'2019-11-28 23:49:57','2025-09-25 15:40:51'),(800,1,112,1,1,1,1,'2019-11-28 23:49:57','2026-03-13 11:12:08'),(801,1,129,1,0,0,0,'2019-11-28 23:49:57','2026-03-13 11:07:02'),(802,1,189,1,0,1,1,'2019-11-28 23:59:22','2025-09-25 15:40:51'),(810,2,1,1,1,1,0,'2019-11-30 02:54:16','2026-01-07 06:11:34'),(817,1,93,1,1,1,1,'2019-11-29 00:56:14','2025-09-25 15:40:51'),(825,1,87,1,0,0,0,'2019-11-29 00:56:14','2026-04-02 06:24:49'),(829,1,94,1,1,0,0,'2019-11-29 00:57:57','2025-09-25 15:40:51'),(836,1,146,1,0,0,0,'2019-11-29 01:13:28','2025-09-25 15:40:51'),(837,1,147,1,0,0,0,'2019-11-29 01:13:28','2025-09-25 15:40:51'),(838,1,148,1,0,0,0,'2019-11-29 01:13:28','2025-09-25 15:40:51'),(839,1,149,1,0,0,0,'2019-11-29 01:13:28','2025-09-25 15:40:51'),(840,1,150,1,0,0,0,'2019-11-29 01:13:28','2025-09-25 15:40:51'),(841,1,151,1,0,0,0,'2019-11-29 01:13:28','2025-09-25 15:40:51'),(842,1,152,1,0,0,0,'2019-11-29 01:13:28','2025-09-25 15:40:51'),(843,1,154,1,0,0,0,'2019-11-29 01:13:28','2025-09-25 15:40:51'),(862,1,155,1,0,0,0,'2019-11-29 02:07:30','2025-09-25 15:40:51'),(863,1,156,1,0,0,0,'2019-11-29 02:07:52','2025-09-25 15:40:51'),(864,1,157,1,0,0,0,'2019-11-29 02:08:05','2025-09-25 15:40:51'),(874,1,158,1,0,0,0,'2019-11-29 02:14:03','2025-09-25 15:40:51'),(875,1,159,1,0,0,0,'2019-11-29 02:14:31','2025-09-25 15:40:51'),(876,1,160,1,0,0,0,'2019-11-29 02:14:44','2025-09-25 15:40:51'),(878,1,162,1,0,0,0,'2019-11-29 02:15:58','2025-09-25 15:40:51'),(879,1,163,1,0,0,0,'2019-11-29 02:16:19','2025-09-25 15:40:51'),(882,1,164,1,0,0,0,'2019-11-29 02:25:17','2025-09-25 15:40:51'),(884,1,165,1,0,0,0,'2019-11-29 02:25:30','2025-09-25 15:40:51'),(886,1,197,1,0,0,0,'2019-11-29 02:25:48','2025-09-25 15:40:51'),(887,1,219,1,0,0,0,'2019-11-29 02:26:05','2025-09-25 15:40:51'),(889,1,220,1,0,0,0,'2019-11-29 02:26:22','2025-09-25 15:40:51'),(932,1,204,1,0,0,0,'2019-11-29 03:43:27','2025-09-25 15:40:51'),(933,1,221,1,0,0,0,'2019-11-29 03:45:04','2025-09-25 15:40:51'),(934,1,178,1,0,0,0,'2019-11-29 03:45:16','2025-09-25 15:40:51'),(935,1,179,1,0,0,0,'2019-11-29 03:45:33','2025-09-25 15:40:51'),(936,1,161,1,0,0,0,'2019-11-29 03:45:48','2025-09-25 15:40:51'),(937,1,180,1,0,0,0,'2019-11-29 03:45:48','2025-09-25 15:40:51'),(938,1,181,1,0,0,0,'2019-11-29 03:49:33','2025-09-25 15:40:51'),(939,1,182,1,0,0,0,'2019-11-29 03:49:45','2025-09-25 15:40:51'),(940,1,183,1,0,0,0,'2019-11-29 03:49:56','2025-09-25 15:40:51'),(941,1,174,1,0,0,0,'2019-11-29 03:50:53','2025-09-25 15:40:51'),(943,1,176,1,0,0,0,'2019-11-29 03:52:10','2025-09-25 15:40:51'),(944,1,177,1,0,0,0,'2019-11-29 03:52:22','2025-09-25 15:40:51'),(945,1,53,0,1,0,0,'2021-01-23 07:09:32','2026-03-02 04:55:08'),(946,1,215,1,0,0,0,'2019-11-29 04:01:37','2025-09-25 15:40:51'),(947,1,213,1,0,0,0,'2019-11-29 04:07:45','2025-09-25 15:40:51'),(974,1,224,1,0,0,0,'2019-11-29 04:32:52','2025-09-25 15:40:51'),(1026,1,135,1,0,1,0,'2019-11-29 06:02:12','2025-09-25 15:40:51'),(1031,1,228,1,0,0,0,'2019-11-29 06:21:16','2025-09-25 15:40:51'),(1083,1,175,1,0,0,0,'2019-11-30 00:37:24','2025-09-25 15:40:51'),(1086,1,43,1,1,1,1,'2019-11-30 00:49:39','2025-09-25 15:40:51'),(1087,1,44,1,0,0,0,'2019-11-30 00:49:39','2025-09-25 15:40:51'),(1088,1,46,1,0,0,0,'2019-11-30 00:49:39','2025-09-25 15:40:51'),(1089,1,217,1,0,0,0,'2019-11-30 00:49:39','2025-09-25 15:40:51'),(1090,1,98,1,1,1,1,'2019-11-30 01:32:51','2025-09-25 15:40:51'),(1091,1,99,1,0,0,0,'2019-11-30 01:30:18','2025-09-25 15:40:51'),(1092,1,223,1,0,0,0,'2019-11-30 01:32:51','2025-09-25 15:40:51'),(1103,2,205,1,1,1,1,'2019-11-30 01:56:04','2025-09-25 15:40:51'),(1105,2,23,1,0,0,0,'2019-11-30 01:56:04','2025-09-25 15:40:51'),(1106,2,24,1,0,0,0,'2019-11-30 01:56:04','2025-09-25 15:40:51'),(1107,2,25,1,0,0,0,'2019-11-30 01:56:04','2025-09-25 15:40:51'),(1108,2,77,1,0,0,0,'2019-11-30 01:56:04','2025-09-25 15:40:51'),(1119,2,117,1,0,0,0,'2019-11-30 01:56:04','2025-09-25 15:40:51'),(1123,3,8,0,0,0,0,'2019-11-30 06:46:18','2025-12-22 06:30:27'),(1125,3,69,1,1,0,0,'2019-11-30 07:00:49','2025-12-22 06:30:40'),(1126,3,70,1,0,0,0,'2019-11-30 07:04:46','2025-12-22 06:22:05'),(1130,3,9,1,1,1,1,'2019-11-30 07:14:54','2025-09-25 15:40:51'),(1131,3,10,1,1,1,1,'2019-11-30 07:16:02','2025-09-25 15:40:51'),(1134,3,35,1,0,0,0,'2019-11-30 07:25:04','2026-03-11 11:43:58'),(1135,3,104,1,0,0,0,'2019-11-30 07:25:53','2026-03-11 11:43:59'),(1140,3,41,1,0,0,0,'2019-11-30 07:37:13','2026-03-11 11:43:49'),(1141,3,42,1,0,0,0,'2019-11-30 07:37:46','2026-03-11 11:43:49'),(1142,3,43,1,0,0,0,'2019-11-30 07:42:06','2026-03-11 11:43:50'),(1151,3,87,1,0,0,0,'2019-11-30 02:23:13','2025-09-25 15:40:51'),(1152,3,88,1,0,0,0,'2019-11-30 02:23:13','2026-03-11 11:42:59'),(1153,3,90,1,0,0,0,'2019-11-30 02:23:13','2026-03-11 11:43:00'),(1154,3,108,1,0,0,0,'2019-11-30 02:23:13','2025-12-22 06:23:33'),(1155,3,109,1,1,0,0,'2019-11-30 02:23:13','2026-03-16 06:45:03'),(1156,3,110,1,0,0,0,'2019-11-30 02:23:13','2026-03-11 11:43:03'),(1157,3,111,1,0,0,0,'2019-11-30 02:23:13','2026-03-11 11:43:03'),(1158,3,112,1,0,0,0,'2019-11-30 02:23:13','2026-03-11 11:43:04'),(1159,3,127,1,0,0,0,'2019-11-30 02:23:13','2025-09-25 15:40:51'),(1160,3,129,1,1,0,0,'2019-11-30 02:23:13','2026-03-18 08:06:30'),(1161,3,102,1,0,0,0,'2019-11-30 02:23:13','2026-03-11 11:43:30'),(1162,3,106,1,0,0,0,'2019-11-30 02:23:13','2025-09-25 15:40:51'),(1163,3,113,1,0,0,0,'2019-11-30 02:23:13','2025-09-25 15:40:51'),(1164,3,114,1,0,0,0,'2019-11-30 02:23:13','2025-09-25 15:40:51'),(1165,3,115,1,0,0,0,'2019-11-30 02:23:13','2025-09-25 15:40:51'),(1166,3,116,1,0,0,0,'2019-11-30 02:23:13','2025-09-25 15:40:51'),(1167,3,117,1,0,0,0,'2019-11-30 02:23:13','2025-09-25 15:40:51'),(1168,3,118,1,0,0,0,'2019-11-30 02:23:13','2025-09-25 15:40:51'),(1171,2,142,1,0,0,0,'2019-11-30 02:36:17','2025-09-25 15:40:51'),(1172,2,144,1,0,0,0,'2019-11-30 02:36:17','2025-09-25 15:40:51'),(1179,2,212,1,0,1,0,'2019-11-30 02:36:17','2025-09-25 15:40:51'),(1183,2,148,1,0,0,0,'2019-11-30 02:36:17','2025-09-25 15:40:51'),(1184,2,149,1,0,0,0,'2019-11-30 02:36:17','2025-09-25 15:40:51'),(1185,2,150,1,0,0,0,'2019-11-30 02:36:17','2025-09-25 15:40:51'),(1186,2,151,1,0,0,0,'2019-11-30 02:36:17','2025-09-25 15:40:51'),(1187,2,152,1,0,0,0,'2019-11-30 02:36:17','2025-09-25 15:40:51'),(1188,2,153,1,0,0,0,'2019-11-30 02:36:17','2025-09-25 15:40:51'),(1189,2,154,1,0,0,0,'2019-11-30 02:36:17','2025-09-25 15:40:51'),(1190,2,197,1,0,0,0,'2019-11-30 02:36:17','2025-09-25 15:40:51'),(1191,2,198,1,0,0,0,'2019-11-30 02:36:17','2025-09-25 15:40:51'),(1193,2,200,1,0,0,0,'2019-11-30 02:36:17','2025-09-25 15:40:51'),(1194,2,201,1,0,0,0,'2019-11-30 02:36:17','2025-09-25 15:40:51'),(1195,2,202,1,0,0,0,'2019-11-30 02:36:17','2025-09-25 15:40:51'),(1196,2,203,1,0,0,0,'2019-11-30 02:36:17','2025-09-25 15:40:51'),(1197,2,219,1,0,0,0,'2019-11-30 02:36:17','2025-09-25 15:40:51'),(1198,2,223,1,0,0,0,'2019-11-30 02:36:17','2025-09-25 15:40:51'),(1199,2,213,1,0,0,0,'2019-11-30 02:36:17','2025-09-25 15:40:51'),(1201,2,230,1,0,0,0,'2019-11-30 02:36:17','2025-09-25 15:40:51'),(1204,2,214,1,0,0,0,'2019-11-30 02:36:17','2026-03-27 07:13:53'),(1206,2,224,1,0,0,0,'2019-11-30 02:36:17','2025-09-25 15:40:51'),(1208,2,2,1,0,0,0,'2019-11-30 02:55:45','2025-09-25 15:40:51'),(1210,2,143,1,1,1,1,'2019-11-30 02:57:28','2025-09-25 15:40:51'),(1211,2,145,1,0,0,0,'2019-11-30 02:57:28','2025-09-25 15:40:51'),(1214,2,3,1,1,1,0,'2019-11-30 03:03:18','2026-01-07 06:11:35'),(1216,2,4,1,1,1,0,'2019-11-30 03:32:56','2026-01-07 06:11:36'),(1218,2,128,0,1,0,0,'2019-11-30 03:37:44','2026-01-07 06:11:38'),(1220,3,135,1,0,0,0,'2019-11-30 07:08:56','2025-12-22 06:31:04'),(1231,3,190,1,0,0,0,'2019-11-30 03:44:02','2025-09-25 15:40:51'),(1232,3,192,1,0,0,0,'2019-11-30 03:44:02','2025-09-25 15:40:51'),(1233,3,226,1,0,0,0,'2019-11-30 03:44:02','2025-09-25 15:40:51'),(1234,3,227,1,0,0,0,'2019-11-30 03:44:02','2025-09-25 15:40:51'),(1235,3,224,1,0,0,0,'2019-11-30 03:44:02','2025-09-25 15:40:51'),(1236,2,15,1,1,1,0,'2019-11-30 03:54:25','2025-09-25 15:40:51'),(1239,2,122,1,0,0,0,'2019-11-30 03:57:48','2025-09-25 15:40:51'),(1240,2,136,1,0,0,0,'2019-11-30 03:57:48','2025-09-25 15:40:51'),(1242,6,217,1,0,0,0,'2019-11-30 04:00:13','2025-09-25 15:40:51'),(1243,6,224,1,0,0,0,'2019-11-30 04:00:13','2025-09-25 15:40:51'),(1245,2,20,1,1,1,1,'2019-11-30 04:01:28','2025-09-25 15:40:51'),(1246,2,137,1,1,1,1,'2019-11-30 04:02:40','2025-09-25 15:40:51'),(1248,2,141,1,1,1,1,'2019-11-30 04:04:04','2025-09-25 15:40:51'),(1250,2,187,1,0,0,0,'2019-11-30 04:11:19','2025-09-25 15:40:51'),(1252,2,207,1,0,0,0,'2019-11-30 04:21:21','2025-09-25 15:40:51'),(1253,2,208,1,0,1,0,'2019-11-30 04:22:00','2025-09-25 15:40:51'),(1255,2,210,1,0,1,0,'2019-11-30 04:22:58','2025-09-25 15:40:51'),(1256,2,211,1,0,1,0,'2019-11-30 04:24:03','2025-09-25 15:40:51'),(1257,2,21,1,0,0,0,'2019-11-30 04:32:59','2025-09-25 15:40:51'),(1259,2,188,1,0,0,0,'2019-11-30 04:34:35','2025-09-25 15:40:51'),(1260,2,27,1,0,0,0,'2019-11-30 04:36:13','2025-09-25 15:40:51'),(1262,2,43,1,1,1,1,'2019-11-30 04:39:42','2025-09-25 15:40:51'),(1263,2,44,1,0,0,0,'2019-11-30 04:41:43','2025-09-25 15:40:51'),(1264,2,46,1,0,0,0,'2019-11-30 04:41:43','2025-09-25 15:40:51'),(1265,2,217,1,0,0,0,'2019-11-30 04:41:43','2025-09-25 15:40:51'),(1266,2,146,1,0,0,0,'2019-11-30 04:46:35','2025-09-25 15:40:51'),(1267,2,147,1,0,0,0,'2019-11-30 04:47:37','2025-09-25 15:40:51'),(1269,2,164,1,0,0,0,'2019-11-30 04:51:04','2025-09-25 15:40:51'),(1271,2,109,1,1,0,0,'2019-11-30 05:03:37','2026-03-16 06:45:03'),(1272,2,93,1,1,1,1,'2019-11-30 05:07:25','2025-09-25 15:40:51'),(1273,2,94,1,1,0,0,'2019-11-30 05:07:42','2025-09-25 15:40:51'),(1275,2,102,1,1,1,1,'2019-11-30 05:11:22','2025-09-25 15:40:51'),(1277,2,196,1,0,0,0,'2019-11-30 05:15:01','2025-09-25 15:40:51'),(1278,2,195,1,0,0,0,'2019-11-30 05:19:08','2025-09-25 15:40:51'),(1279,2,185,1,0,0,0,'2019-11-30 05:21:44','2026-03-27 07:13:51'),(1280,2,186,1,0,0,0,'2019-11-30 05:22:43','2026-03-27 07:13:52'),(1281,2,222,1,0,0,0,'2019-11-30 05:24:30','2026-03-27 07:13:55'),(1283,3,5,1,1,0,1,'2019-11-30 06:43:04','2025-12-22 06:30:45'),(1284,3,6,1,0,0,0,'2019-11-30 06:43:29','2025-09-25 15:40:51'),(1285,3,7,0,0,0,0,'2019-11-30 06:44:39','2025-12-22 06:30:26'),(1286,3,68,0,0,0,0,'2019-11-30 06:46:58','2025-12-22 06:30:32'),(1287,3,71,1,0,0,0,'2019-11-30 07:05:41','2025-09-25 15:40:51'),(1288,3,73,1,0,0,0,'2019-11-30 07:05:59','2025-09-25 15:40:51'),(1289,3,74,1,0,0,0,'2019-11-30 07:06:08','2025-09-25 15:40:51'),(1290,3,11,1,0,0,0,'2019-11-30 07:16:37','2025-09-25 15:40:51'),(1291,3,12,1,1,1,1,'2019-11-30 07:19:29','2025-09-25 15:40:51'),(1292,3,13,1,1,1,1,'2019-11-30 07:22:27','2025-09-25 15:40:51'),(1294,3,14,1,0,0,0,'2019-11-30 07:22:55','2025-09-25 15:40:51'),(1295,3,31,1,0,0,0,'2019-12-02 06:30:37','2025-12-22 06:22:22'),(1297,3,37,1,0,0,0,'2019-11-30 07:28:09','2026-03-11 11:43:52'),(1298,3,38,1,0,0,0,'2019-11-30 07:29:02','2026-03-11 11:43:52'),(1299,3,39,1,0,0,0,'2019-11-30 07:30:07','2026-03-11 11:43:51'),(1300,3,40,1,0,0,0,'2019-11-30 07:32:43','2026-03-11 11:43:48'),(1301,3,44,1,0,0,0,'2019-11-30 07:44:09','2025-09-25 15:40:51'),(1302,3,46,1,0,0,0,'2019-11-30 07:44:09','2025-09-25 15:40:51'),(1303,3,217,1,0,0,0,'2019-11-30 07:44:09','2025-09-25 15:40:51'),(1304,3,155,1,0,0,0,'2019-11-30 07:44:32','2025-09-25 15:40:51'),(1305,3,156,1,0,0,0,'2019-11-30 07:45:18','2025-09-25 15:40:51'),(1306,3,157,1,0,0,0,'2019-11-30 07:45:42','2025-09-25 15:40:51'),(1307,3,158,1,0,0,0,'2019-11-30 07:46:07','2025-09-25 15:40:51'),(1308,3,159,1,0,0,0,'2019-11-30 07:46:21','2025-09-25 15:40:51'),(1309,3,160,1,0,0,0,'2019-11-30 07:46:33','2025-09-25 15:40:51'),(1313,3,161,1,0,0,0,'2019-11-30 07:48:26','2025-09-25 15:40:51'),(1314,3,162,1,0,0,0,'2019-11-30 07:48:48','2025-09-25 15:40:51'),(1315,3,163,1,0,0,0,'2019-11-30 07:48:48','2025-09-25 15:40:51'),(1316,3,164,1,0,0,0,'2019-11-30 07:49:47','2025-09-25 15:40:51'),(1317,3,165,1,0,0,0,'2019-11-30 07:49:47','2025-09-25 15:40:51'),(1318,3,174,1,0,0,0,'2019-11-30 07:49:47','2025-09-25 15:40:51'),(1319,3,175,1,0,0,0,'2019-11-30 07:49:59','2025-09-25 15:40:51'),(1320,3,181,1,0,0,0,'2019-11-30 07:50:08','2025-09-25 15:40:51'),(1321,3,86,1,1,1,0,'2019-11-30 07:54:08','2026-03-18 08:05:55'),(1322,4,28,1,1,1,1,'2019-12-01 00:52:39','2025-09-25 15:40:51'),(1324,4,29,1,0,0,0,'2019-12-01 00:53:46','2025-09-25 15:40:51'),(1325,4,30,1,0,0,0,'2019-12-01 00:53:59','2025-09-25 15:40:51'),(1326,4,123,1,0,0,0,'2019-12-01 00:54:26','2025-09-25 15:40:51'),(1327,4,228,1,0,0,0,'2019-12-01 00:54:39','2025-09-25 15:40:51'),(1328,4,43,1,1,1,1,'2019-12-01 00:58:05','2025-09-25 15:40:51'),(1332,4,44,1,0,0,0,'2019-12-01 00:59:16','2025-09-25 15:40:51'),(1333,4,46,1,0,0,0,'2019-12-01 00:59:16','2025-09-25 15:40:51'),(1334,4,217,1,0,0,0,'2019-12-01 00:59:16','2025-09-25 15:40:51'),(1335,4,178,1,0,0,0,'2019-12-01 00:59:59','2025-09-25 15:40:51'),(1336,4,179,1,0,0,0,'2019-12-01 01:00:11','2025-09-25 15:40:51'),(1337,4,180,1,0,0,0,'2019-12-01 01:00:29','2025-09-25 15:40:51'),(1338,4,221,1,0,0,0,'2019-12-01 01:00:46','2025-09-25 15:40:51'),(1339,4,86,1,0,0,0,'2019-12-01 01:01:02','2025-09-25 15:40:51'),(1341,4,106,1,0,0,0,'2019-12-01 01:05:21','2025-09-25 15:40:51'),(1342,1,107,1,0,0,0,'2019-12-01 01:06:44','2025-09-25 15:40:51'),(1343,4,117,1,0,0,0,'2019-12-01 01:10:20','2025-09-25 15:40:51'),(1344,4,194,1,0,0,0,'2019-12-01 01:11:35','2025-09-25 15:40:51'),(1348,4,230,1,0,0,0,'2019-12-01 01:19:15','2025-09-25 15:40:51'),(1350,6,1,1,0,0,0,'2019-12-01 01:35:32','2025-09-25 15:40:51'),(1351,6,21,1,0,0,0,'2019-12-01 01:36:29','2025-09-25 15:40:51'),(1352,6,23,1,0,0,0,'2019-12-01 01:36:45','2025-09-25 15:40:51'),(1353,6,24,1,0,0,0,'2019-12-01 01:37:05','2025-09-25 15:40:51'),(1354,6,25,1,0,0,0,'2019-12-01 01:37:34','2025-09-25 15:40:51'),(1355,6,77,1,0,0,0,'2019-12-01 01:38:08','2025-09-25 15:40:51'),(1356,6,188,1,0,0,0,'2019-12-01 01:38:45','2025-09-25 15:40:51'),(1357,6,43,1,1,1,1,'2019-12-01 01:40:44','2025-09-25 15:40:51'),(1358,6,78,1,1,1,1,'2019-12-01 01:43:04','2025-09-25 15:40:51'),(1360,6,79,1,1,0,1,'2019-12-01 01:44:39','2025-09-25 15:40:51'),(1361,6,80,1,1,1,1,'2019-12-01 01:45:08','2025-09-25 15:40:51'),(1362,6,81,1,1,1,1,'2019-12-01 01:47:50','2025-09-25 15:40:51'),(1363,6,85,1,1,1,1,'2019-12-01 01:50:43','2025-09-25 15:40:51'),(1364,6,86,1,0,0,0,'2019-12-01 01:51:10','2025-09-25 15:40:51'),(1365,6,106,1,0,0,0,'2019-12-01 01:52:55','2025-09-25 15:40:51'),(1366,6,117,1,0,0,0,'2019-12-01 01:53:08','2025-09-25 15:40:51'),(1394,1,106,1,0,0,0,'2019-12-02 05:20:33','2025-09-25 15:40:51'),(1395,1,113,1,0,0,0,'2019-12-02 05:20:59','2025-09-25 15:40:51'),(1396,1,114,1,0,0,0,'2019-12-02 05:21:34','2025-09-25 15:40:51'),(1397,1,115,1,0,0,0,'2019-12-02 05:21:34','2025-09-25 15:40:51'),(1398,1,116,1,0,0,0,'2019-12-02 05:21:54','2025-09-25 15:40:51'),(1399,1,117,1,0,0,0,'2019-12-02 05:22:04','2025-09-25 15:40:51'),(1400,1,118,1,0,0,0,'2019-12-02 05:22:20','2025-09-25 15:40:51'),(1402,1,191,1,0,0,0,'2019-12-02 05:23:34','2025-09-25 15:40:51'),(1403,1,192,1,0,0,0,'2019-12-02 05:23:47','2025-09-25 15:40:51'),(1404,1,193,1,0,0,0,'2019-12-02 05:23:58','2025-09-25 15:40:51'),(1405,1,194,1,0,0,0,'2019-12-02 05:24:11','2025-09-25 15:40:51'),(1406,1,195,1,0,0,0,'2019-12-02 05:24:20','2025-09-25 15:40:51'),(1408,1,227,1,0,0,0,'2019-12-02 05:25:47','2025-09-25 15:40:51'),(1410,1,226,1,0,0,0,'2019-12-02 05:31:41','2025-09-25 15:40:51'),(1411,1,229,1,0,0,0,'2019-12-02 05:32:57','2025-09-25 15:40:51'),(1412,1,230,1,0,0,0,'2019-12-02 05:32:57','2025-09-25 15:40:51'),(1413,1,190,1,0,0,0,'2019-12-02 05:43:41','2025-09-25 15:40:51'),(1414,2,174,1,0,0,0,'2019-12-02 05:54:37','2025-09-25 15:40:51'),(1415,2,175,1,0,0,0,'2019-12-02 05:54:37','2025-09-25 15:40:51'),(1418,2,232,0,0,0,0,'2019-12-02 06:11:27','2026-03-27 07:14:08'),(1419,2,231,1,0,0,0,'2019-12-02 06:12:28','2025-09-25 15:40:51'),(1420,1,231,1,1,1,1,'2021-01-23 07:09:32','2025-09-25 15:40:51'),(1421,1,232,1,0,1,1,'2019-12-02 06:19:32','2025-09-25 15:40:51'),(1422,3,32,1,0,0,0,'2019-12-02 06:30:37','2026-03-11 11:43:55'),(1423,3,33,1,0,0,0,'2019-12-02 06:30:37','2026-03-11 11:43:56'),(1424,3,34,1,0,0,0,'2019-12-02 06:30:37','2026-03-11 11:43:58'),(1425,3,182,1,0,0,0,'2019-12-02 06:30:37','2025-09-25 15:40:51'),(1426,3,183,1,0,0,0,'2019-12-02 06:30:37','2025-09-25 15:40:51'),(1427,3,189,1,0,0,0,'2019-12-02 06:30:37','2025-12-22 06:23:43'),(1428,3,229,1,0,0,0,'2019-12-02 06:30:37','2025-09-25 15:40:51'),(1429,3,230,1,0,0,0,'2019-12-02 06:30:37','2025-09-25 15:40:51'),(1430,4,213,1,0,0,0,'2019-12-02 06:32:14','2025-09-25 15:40:51'),(1432,4,224,1,0,0,0,'2019-12-02 06:32:14','2025-09-25 15:40:51'),(1433,4,195,1,0,0,0,'2019-12-03 04:57:53','2025-09-25 15:40:51'),(1434,4,229,1,0,0,0,'2019-12-03 04:58:19','2025-09-25 15:40:51'),(1436,6,213,1,0,0,0,'2019-12-03 05:10:11','2025-09-25 15:40:51'),(1437,6,191,1,0,0,0,'2019-12-03 05:10:11','2025-09-25 15:40:51'),(1438,6,193,1,0,0,0,'2019-12-03 05:10:11','2025-09-25 15:40:51'),(1439,6,230,1,0,0,0,'2019-12-03 05:10:11','2025-09-25 15:40:51'),(1440,2,106,1,0,0,0,'2020-01-25 04:21:36','2025-09-25 15:40:51'),(1441,2,107,1,0,0,0,'2020-02-12 02:10:13','2025-09-25 15:40:51'),(1442,2,134,1,1,1,0,'2020-02-12 02:12:36','2026-01-07 06:11:39'),(1443,1,233,1,0,0,0,'2020-02-12 02:21:57','2025-09-25 15:40:51'),(1444,2,86,0,0,0,0,'2020-02-12 02:22:33','2026-03-27 10:00:29'),(1445,3,233,1,0,0,0,'2020-02-12 03:51:17','2025-09-25 15:40:51'),(1446,1,234,1,1,1,1,'2020-06-01 21:51:09','2025-09-25 15:40:51'),(1447,1,235,1,1,1,1,'2020-05-29 23:17:01','2025-09-25 15:40:51'),(1448,1,236,1,1,1,0,'2020-05-29 23:17:52','2025-09-25 15:40:51'),(1449,1,237,1,0,1,0,'2020-05-29 23:18:18','2025-09-25 15:40:51'),(1450,1,238,1,1,1,1,'2020-05-29 23:19:52','2025-09-25 15:40:51'),(1451,1,239,1,1,1,1,'2020-05-29 23:22:10','2025-09-25 15:40:51'),(1452,2,236,1,1,1,0,'2020-05-29 23:40:33','2025-09-25 15:40:51'),(1453,2,237,1,0,1,0,'2020-05-29 23:40:33','2025-09-25 15:40:51'),(1454,2,238,1,1,1,1,'2020-05-29 23:40:33','2025-09-25 15:40:51'),(1455,2,239,1,1,1,1,'2020-05-29 23:40:33','2025-09-25 15:40:51'),(1456,2,240,1,0,0,0,'2020-05-28 20:51:18','2025-09-25 15:40:51'),(1457,2,241,1,0,0,0,'2020-05-28 20:51:18','2025-09-25 15:40:51'),(1458,1,240,1,0,0,0,'2020-06-07 18:30:42','2025-09-25 15:40:51'),(1459,1,241,1,0,0,0,'2020-06-07 18:30:42','2025-09-25 15:40:51'),(1460,1,242,1,0,0,0,'2020-06-07 18:30:42','2025-09-25 15:40:51'),(1461,2,242,1,0,0,0,'2020-06-11 22:45:24','2025-09-25 15:40:51'),(1462,3,242,1,0,0,0,'2020-06-14 22:46:54','2025-09-25 15:40:51'),(1463,6,242,1,0,0,0,'2020-06-14 22:48:14','2025-09-25 15:40:51'),(1464,1,243,1,0,0,0,'2020-09-12 06:05:45','2025-09-25 15:40:51'),(1465,1,109,1,1,0,0,'2020-09-21 06:33:50','2026-03-16 06:45:03'),(1466,1,108,1,0,1,1,'2023-11-04 12:52:08','2025-09-25 15:40:51'),(1467,1,244,1,0,0,0,'2020-09-21 06:59:54','2025-09-25 15:40:51'),(1468,1,245,1,0,0,0,'2020-09-21 06:59:54','2025-09-25 15:40:51'),(1469,1,246,1,0,0,0,'2020-09-21 06:59:54','2025-09-25 15:40:51'),(1470,1,247,1,0,0,0,'2021-01-07 06:12:14','2025-09-25 15:40:51'),(1472,2,247,0,0,0,0,'2021-01-21 12:46:40','2026-03-27 07:13:39'),(1473,1,248,1,1,1,1,'2021-05-19 12:52:49','2025-09-25 15:40:51'),(1474,1,249,1,0,0,0,'2021-05-19 12:52:49','2025-09-25 15:40:51'),(1475,2,248,1,1,1,1,'2021-05-28 13:11:52','2025-09-25 15:40:51'),(1476,3,248,1,0,0,0,'2021-05-28 09:36:16','2026-03-11 11:43:29'),(1477,3,249,1,0,0,0,'2021-05-28 09:36:16','2025-09-25 15:40:51'),(1478,6,248,1,0,0,0,'2021-05-28 09:56:14','2025-09-25 15:40:51'),(1479,6,249,1,0,0,0,'2021-05-28 09:56:14','2025-09-25 15:40:51'),(1480,2,249,1,0,0,0,'2021-05-28 13:11:52','2025-09-25 15:40:51'),(1481,1,269,1,0,0,1,'2023-11-04 12:52:08','2025-09-25 15:40:51'),(1482,2,269,1,0,0,1,'2023-11-04 12:52:28','2025-09-25 15:40:51'),(1483,3,269,0,0,0,0,'2023-11-04 12:53:22','2025-12-22 06:31:21'),(1484,4,269,1,0,0,1,'2023-11-04 12:53:34','2025-09-25 15:40:51'),(1485,6,269,1,0,0,1,'2023-11-04 12:53:52','2025-09-25 15:40:51'),(1486,1,10001,1,0,0,0,'2022-05-05 01:30:06','2025-11-08 01:23:13'),(1487,1,10002,1,0,0,0,'2022-05-05 01:20:12','2025-11-08 01:23:13'),(1488,1,10003,1,0,0,0,'2022-05-05 01:20:12','2025-11-08 01:23:13'),(1489,1,10004,1,0,0,0,'2022-05-05 01:20:12','2025-11-08 01:23:13'),(1490,1,10005,1,0,0,0,'2022-05-05 01:20:12','2025-11-08 01:23:13'),(1491,1,10006,1,0,0,0,'2022-05-05 01:20:12','2025-11-08 01:23:13'),(1492,1,10007,0,0,0,0,'2022-05-05 01:20:12','2026-03-02 04:54:53'),(1493,3,1,1,NULL,NULL,NULL,'2025-12-22 06:57:03','2025-12-22 06:57:03'),(1494,3,2,1,NULL,NULL,NULL,'2025-12-22 06:57:04','2025-12-22 06:57:04'),(1495,3,3,1,NULL,NULL,NULL,'2025-12-22 06:57:06','2025-12-22 06:57:06'),(1496,3,4,1,NULL,NULL,NULL,'2025-12-22 06:57:07','2025-12-22 06:57:07'),(1497,3,107,1,NULL,NULL,NULL,'2025-12-22 06:57:09','2025-12-22 06:57:09'),(1498,3,128,1,NULL,NULL,NULL,'2025-12-22 06:57:10','2025-12-22 06:57:10'),(1499,3,134,1,NULL,NULL,NULL,'2025-12-22 06:57:11','2025-12-22 06:57:11'),(1500,3,258,1,NULL,NULL,NULL,'2026-01-07 07:36:16','2026-01-07 07:36:16'),(1501,4,10008,1,1,1,NULL,'2026-01-21 09:38:33','2026-03-16 09:58:03'),(1502,4,10009,1,1,1,NULL,'2026-01-21 09:38:40','2026-03-16 09:58:03'),(1503,4,10010,1,1,1,NULL,'2026-01-21 09:38:45','2026-03-16 09:58:03'),(1504,4,10011,1,1,1,NULL,'2026-01-21 09:38:48','2026-03-16 09:58:03'),(1505,4,10012,1,1,1,NULL,'2026-01-21 09:38:51','2026-01-21 09:38:55'),(1506,4,10013,1,1,1,NULL,'2026-01-21 09:38:58','2026-01-21 09:39:00'),(1507,4,10014,1,1,1,NULL,'2026-01-21 09:39:01','2026-01-21 09:39:05'),(1508,4,10015,1,1,1,NULL,'2026-01-21 09:39:06','2026-01-21 09:39:09'),(1509,4,10016,1,1,1,NULL,'2026-01-21 09:39:08','2026-01-21 09:39:13'),(1510,4,10017,1,1,1,NULL,'2026-01-21 09:39:09','2026-01-21 09:39:14'),(1511,4,10018,1,1,1,NULL,'2026-01-21 09:39:15','2026-01-21 09:39:18'),(1512,4,10019,1,1,1,NULL,'2026-01-21 09:39:16','2026-01-21 09:39:19'),(1513,4,10020,1,1,1,NULL,'2026-01-21 09:39:21','2026-03-16 09:58:03'),(1514,4,10021,1,1,1,NULL,'2026-01-21 09:39:23','2026-03-16 09:58:03'),(1515,4,10022,1,1,1,NULL,'2026-01-21 09:39:25','2026-03-16 09:58:03'),(1516,8,1,1,NULL,NULL,NULL,'2026-01-24 11:27:24','2026-01-24 11:27:24'),(1517,8,10008,0,NULL,NULL,NULL,'2026-01-24 11:28:10','2026-01-24 11:28:17'),(1518,8,43,1,1,1,1,'2026-01-24 11:28:32','2026-01-24 11:36:05'),(1519,8,44,1,NULL,NULL,NULL,'2026-01-24 11:28:35','2026-01-24 11:36:08'),(1520,8,146,0,NULL,NULL,NULL,'2026-01-24 11:28:49','2026-01-24 11:36:26'),(1521,8,78,1,1,1,1,'2026-01-24 11:29:04','2026-01-24 11:29:07'),(1522,8,79,1,1,NULL,1,'2026-01-24 11:29:10','2026-01-24 11:29:12'),(1523,8,86,1,0,0,NULL,'2026-01-24 11:29:27','2026-01-24 11:38:20'),(1524,8,88,0,NULL,NULL,NULL,'2026-01-24 11:29:48','2026-01-24 11:38:24'),(1525,8,90,0,NULL,NULL,NULL,'2026-01-24 11:29:51','2026-01-24 11:38:24'),(1526,8,108,0,NULL,NULL,NULL,'2026-01-24 11:29:54','2026-01-24 11:38:26'),(1527,8,102,1,0,0,0,'2026-01-24 11:30:06','2026-03-04 22:33:06'),(1528,8,191,1,NULL,NULL,NULL,'2026-01-24 11:30:27','2026-01-24 11:30:27'),(1529,8,194,0,NULL,NULL,NULL,'2026-01-24 11:30:34','2026-01-24 11:40:18'),(1530,8,224,1,NULL,NULL,NULL,'2026-01-24 11:30:45','2026-01-24 11:30:45'),(1531,8,274,1,0,NULL,NULL,'2026-01-24 11:30:50','2026-01-24 11:30:53'),(1532,8,21,1,NULL,NULL,NULL,'2026-01-24 11:35:07','2026-01-24 11:35:15'),(1533,8,23,1,NULL,NULL,NULL,'2026-01-24 11:35:16','2026-01-24 11:35:16'),(1534,8,24,1,NULL,NULL,NULL,'2026-01-24 11:35:18','2026-01-24 11:35:18'),(1535,8,25,1,NULL,NULL,NULL,'2026-01-24 11:35:20','2026-01-24 11:35:20'),(1536,8,188,1,NULL,NULL,NULL,'2026-01-24 11:35:31','2026-01-24 11:35:31'),(1537,8,77,1,NULL,NULL,NULL,'2026-01-24 11:35:34','2026-01-24 11:35:34'),(1538,8,269,1,NULL,NULL,1,'2026-01-24 11:35:39','2026-01-24 11:35:43'),(1539,8,46,1,NULL,NULL,NULL,'2026-01-24 11:36:09','2026-01-24 11:36:09'),(1540,8,217,1,NULL,NULL,NULL,'2026-01-24 11:36:11','2026-01-24 11:36:11'),(1541,8,242,1,NULL,NULL,NULL,'2026-01-24 11:36:35','2026-01-24 11:36:35'),(1542,8,213,1,NULL,NULL,NULL,'2026-01-24 11:36:56','2026-01-24 11:36:56'),(1543,8,109,1,1,NULL,NULL,'2026-01-24 11:38:30','2026-03-16 06:45:03'),(1544,8,189,1,NULL,NULL,NULL,'2026-01-24 11:39:10','2026-01-24 11:39:14'),(1545,8,106,1,NULL,NULL,NULL,'2026-01-24 11:39:36','2026-01-24 11:39:36'),(1546,8,117,1,NULL,NULL,NULL,'2026-01-24 11:39:42','2026-01-24 11:39:42'),(1547,8,118,1,NULL,NULL,NULL,'2026-01-24 11:39:44','2026-01-24 11:39:44'),(1548,8,193,1,NULL,NULL,NULL,'2026-01-24 11:40:06','2026-01-24 11:40:17'),(1549,8,195,1,NULL,NULL,NULL,'2026-01-24 11:40:10','2026-01-24 11:40:10'),(1550,8,230,1,NULL,NULL,NULL,'2026-01-24 11:40:27','2026-01-24 11:40:27'),(1551,8,229,1,NULL,NULL,NULL,'2026-01-24 11:40:28','2026-01-24 11:40:28'),(1552,8,280,1,NULL,NULL,NULL,'2026-01-24 11:40:31','2026-01-24 11:40:31'),(1553,8,232,1,NULL,1,1,'2026-01-27 08:01:37','2026-03-04 22:33:16'),(1554,1,10023,0,1,0,0,'2026-01-28 07:41:18','2026-01-28 07:41:18'),(1555,8,276,1,NULL,NULL,NULL,'2026-01-28 12:30:24','2026-01-28 12:30:24'),(1556,1,283,1,1,1,1,'2026-02-02 07:07:35','2026-02-02 07:07:35'),(1557,1,284,1,1,1,1,'2026-02-02 07:07:35','2026-02-02 07:07:35'),(1558,1,285,1,1,1,1,'2026-02-02 07:07:35','2026-02-02 07:07:35'),(1559,1,286,1,1,1,1,'2026-02-02 07:07:35','2026-02-02 07:07:35'),(1560,3,283,1,1,1,1,'2026-02-02 07:07:35','2026-02-02 07:07:35'),(1561,3,284,1,1,1,1,'2026-02-02 07:07:35','2026-02-02 07:07:35'),(1562,3,285,1,1,1,1,'2026-02-02 07:07:35','2026-02-02 07:07:35'),(1563,3,286,1,0,0,0,'2026-02-02 07:07:35','2026-02-02 07:07:35'),(1564,6,285,1,1,0,0,'2026-02-02 07:07:35','2026-02-02 07:07:35'),(1568,1,10025,1,1,0,1,'2026-02-04 15:18:19','2026-03-13 11:12:34'),(1569,7,10026,1,0,1,0,'2026-02-10 18:20:47','2026-02-10 18:20:47'),(1570,7,10027,1,0,1,0,'2026-02-10 18:48:06','2026-02-10 18:48:06'),(1571,8,10023,NULL,1,NULL,NULL,'2026-02-11 12:42:04','2026-02-11 12:42:04'),(1573,6,232,1,1,1,1,'2026-02-11 20:29:16','2026-02-11 20:29:16'),(1574,8,151,1,NULL,NULL,NULL,'2026-02-12 07:34:30','2026-02-12 07:34:30'),(1575,9,106,1,NULL,NULL,NULL,'2026-02-16 17:36:34','2026-02-16 17:36:34'),(1576,9,113,1,NULL,NULL,NULL,'2026-02-16 17:36:35','2026-02-16 17:36:35'),(1577,9,114,1,NULL,NULL,NULL,'2026-02-16 17:36:36','2026-02-16 17:36:36'),(1578,9,115,1,NULL,NULL,NULL,'2026-02-16 17:36:36','2026-02-16 17:36:36'),(1579,9,116,1,NULL,NULL,NULL,'2026-02-16 17:36:37','2026-02-16 17:36:37'),(1580,9,117,1,NULL,NULL,NULL,'2026-02-16 17:36:38','2026-02-16 17:36:38'),(1581,9,118,1,NULL,NULL,NULL,'2026-02-16 17:36:39','2026-02-16 17:36:39'),(1582,9,190,1,NULL,NULL,NULL,'2026-02-16 17:36:39','2026-02-16 17:36:39'),(1583,9,191,1,NULL,NULL,NULL,'2026-02-16 17:36:40','2026-02-16 17:36:40'),(1584,9,192,1,NULL,NULL,NULL,'2026-02-16 17:36:42','2026-02-16 17:36:42'),(1585,9,193,1,NULL,NULL,NULL,'2026-02-16 17:36:43','2026-02-16 17:36:43'),(1586,9,194,1,NULL,NULL,NULL,'2026-02-16 17:36:44','2026-02-16 17:36:44'),(1587,9,195,1,NULL,NULL,NULL,'2026-02-16 17:36:45','2026-02-16 17:36:45'),(1588,9,226,1,NULL,NULL,NULL,'2026-02-16 17:36:46','2026-02-16 17:36:46'),(1589,9,227,1,NULL,NULL,NULL,'2026-02-16 17:36:46','2026-02-16 17:36:46'),(1590,9,229,1,NULL,NULL,NULL,'2026-02-16 17:36:47','2026-02-16 17:36:47'),(1591,9,230,1,NULL,NULL,NULL,'2026-02-16 17:36:48','2026-02-16 17:36:48'),(1592,9,280,1,NULL,NULL,NULL,'2026-02-16 17:36:50','2026-02-16 17:36:50'),(1593,9,281,1,NULL,NULL,NULL,'2026-02-16 17:36:50','2026-02-16 17:36:50'),(1594,9,282,1,NULL,NULL,NULL,'2026-02-16 17:36:51','2026-02-16 17:36:51'),(1595,9,10029,1,NULL,NULL,NULL,'2026-02-16 17:36:52','2026-02-16 17:36:52'),(1596,9,102,1,NULL,NULL,NULL,'2026-02-17 05:16:19','2026-02-17 05:16:19'),(1597,9,274,1,NULL,NULL,NULL,'2026-02-17 05:16:30','2026-02-17 05:16:30'),(1598,1,276,1,NULL,NULL,NULL,'2026-03-02 04:53:13','2026-03-02 04:53:13'),(1599,1,258,1,NULL,NULL,NULL,'2026-03-02 04:53:15','2026-03-02 04:53:15'),(1600,1,257,1,NULL,NULL,NULL,'2026-03-02 04:53:16','2026-03-02 04:53:16'),(1601,1,256,1,NULL,NULL,NULL,'2026-03-02 04:53:17','2026-03-02 04:53:17'),(1602,1,274,1,NULL,NULL,NULL,'2026-03-02 04:53:31','2026-03-02 04:53:31'),(1603,1,275,1,NULL,NULL,NULL,'2026-03-02 04:53:33','2026-03-02 04:53:33'),(1604,1,277,1,NULL,NULL,NULL,'2026-03-02 04:53:33','2026-03-02 04:53:33'),(1605,1,278,1,NULL,NULL,NULL,'2026-03-02 04:53:34','2026-03-02 04:53:34'),(1606,1,279,1,NULL,NULL,NULL,'2026-03-02 04:53:35','2026-03-02 04:53:35'),(1607,1,273,1,NULL,NULL,NULL,'2026-03-02 04:53:36','2026-03-02 04:53:36'),(1608,1,263,1,NULL,NULL,NULL,'2026-03-02 04:53:37','2026-03-02 04:53:37'),(1609,1,280,1,NULL,NULL,NULL,'2026-03-02 04:53:42','2026-03-02 04:53:42'),(1610,1,281,1,NULL,NULL,NULL,'2026-03-02 04:53:42','2026-03-02 04:53:42'),(1611,1,282,1,NULL,NULL,NULL,'2026-03-02 04:53:43','2026-03-02 04:53:43'),(1612,1,10029,1,NULL,NULL,NULL,'2026-03-02 04:53:44','2026-03-02 04:53:44'),(1613,1,250,1,NULL,NULL,NULL,'2026-03-02 04:53:48','2026-03-02 04:53:48'),(1614,1,10027,1,NULL,NULL,NULL,'2026-03-02 04:53:49','2026-03-02 04:53:49'),(1615,1,10026,1,NULL,NULL,NULL,'2026-03-02 04:53:50','2026-03-02 04:53:50'),(1616,1,264,1,NULL,NULL,NULL,'2026-03-02 04:53:54','2026-03-02 04:53:54'),(1617,1,265,1,NULL,NULL,NULL,'2026-03-02 04:53:55','2026-03-02 04:53:55'),(1618,1,271,1,NULL,NULL,NULL,'2026-03-02 04:53:56','2026-03-02 04:53:56'),(1619,1,10028,1,NULL,NULL,NULL,'2026-03-02 04:53:57','2026-03-02 04:53:57'),(1620,1,252,1,NULL,NULL,NULL,'2026-03-02 04:54:02','2026-03-02 04:54:02'),(1621,1,254,1,NULL,NULL,NULL,'2026-03-02 04:54:03','2026-03-02 04:54:03'),(1622,1,253,1,NULL,NULL,NULL,'2026-03-02 04:54:04','2026-03-02 04:54:04'),(1623,1,255,1,NULL,NULL,NULL,'2026-03-02 04:54:05','2026-03-02 04:54:05'),(1624,1,262,1,NULL,NULL,NULL,'2026-03-02 04:54:07','2026-03-02 04:54:07'),(1625,1,261,1,NULL,NULL,NULL,'2026-03-02 04:54:09','2026-03-02 04:54:09'),(1626,1,260,1,NULL,NULL,NULL,'2026-03-02 04:54:09','2026-03-02 04:54:09'),(1627,1,259,1,NULL,NULL,NULL,'2026-03-02 04:54:10','2026-03-02 04:54:10'),(1628,1,270,1,NULL,NULL,NULL,'2026-03-02 04:54:14','2026-03-02 04:54:14'),(1629,1,10008,1,NULL,NULL,NULL,'2026-03-02 04:54:14','2026-03-02 04:54:14'),(1630,1,10009,1,NULL,NULL,NULL,'2026-03-02 04:54:15','2026-03-02 04:54:15'),(1631,1,10010,1,NULL,NULL,NULL,'2026-03-02 04:54:16','2026-03-02 04:54:16'),(1632,1,10011,1,NULL,NULL,NULL,'2026-03-02 04:54:16','2026-03-02 04:54:16'),(1633,1,10012,1,NULL,NULL,NULL,'2026-03-02 04:54:17','2026-03-02 04:54:17'),(1634,1,10013,1,NULL,NULL,NULL,'2026-03-02 04:54:18','2026-03-02 04:54:18'),(1635,1,10014,1,NULL,NULL,NULL,'2026-03-02 04:54:18','2026-03-02 04:54:18'),(1636,1,10015,1,NULL,NULL,NULL,'2026-03-02 04:54:19','2026-03-02 04:54:19'),(1637,1,10016,1,NULL,NULL,NULL,'2026-03-02 04:54:20','2026-03-02 04:54:20'),(1638,1,10017,1,NULL,NULL,NULL,'2026-03-02 04:54:20','2026-03-02 04:54:20'),(1639,1,10018,1,NULL,NULL,NULL,'2026-03-02 04:54:22','2026-03-02 04:54:22'),(1640,1,10019,1,NULL,NULL,NULL,'2026-03-02 04:54:22','2026-03-02 04:54:22'),(1641,1,10020,1,NULL,NULL,NULL,'2026-03-02 04:54:23','2026-03-02 04:54:23'),(1642,1,10021,1,NULL,NULL,NULL,'2026-03-02 04:54:24','2026-03-02 04:54:24'),(1643,1,10022,1,NULL,NULL,NULL,'2026-03-02 04:54:24','2026-03-02 04:54:24'),(1644,1,268,1,NULL,NULL,NULL,'2026-03-02 04:54:27','2026-03-02 04:54:27'),(1645,1,251,1,NULL,NULL,NULL,'2026-03-02 04:54:30','2026-03-02 04:54:30'),(1646,1,266,1,NULL,NULL,NULL,'2026-03-02 04:54:31','2026-03-02 04:54:31'),(1647,1,267,1,NULL,NULL,NULL,'2026-03-02 04:54:32','2026-03-02 04:54:32'),(1648,1,272,1,NULL,NULL,NULL,'2026-03-02 04:54:36','2026-03-02 04:54:36'),(1649,9,1,1,0,NULL,NULL,'2026-03-02 10:05:56','2026-03-02 10:06:00'),(1650,9,2,1,NULL,NULL,NULL,'2026-03-02 10:05:58','2026-03-02 10:05:58'),(1651,9,3,1,NULL,NULL,NULL,'2026-03-02 10:06:01','2026-03-02 10:06:01'),(1652,9,4,1,NULL,NULL,NULL,'2026-03-02 10:06:02','2026-03-02 10:06:02'),(1653,9,107,1,NULL,NULL,NULL,'2026-03-02 10:06:03','2026-03-02 10:06:03'),(1654,9,128,1,NULL,NULL,NULL,'2026-03-02 10:06:04','2026-03-02 10:06:04'),(1655,9,134,1,NULL,NULL,NULL,'2026-03-02 10:06:05','2026-03-02 10:06:05'),(1656,9,5,1,NULL,NULL,NULL,'2026-03-02 10:06:07','2026-03-02 10:06:07'),(1657,9,6,1,NULL,NULL,NULL,'2026-03-02 10:06:07','2026-03-02 10:06:07'),(1658,9,7,1,NULL,NULL,NULL,'2026-03-02 10:06:08','2026-03-02 10:06:08'),(1659,9,8,1,NULL,NULL,NULL,'2026-03-02 10:06:15','2026-03-02 10:06:15'),(1660,9,68,1,NULL,NULL,NULL,'2026-03-02 10:06:16','2026-03-02 10:06:16'),(1661,9,69,1,NULL,NULL,NULL,'2026-03-02 10:06:16','2026-03-02 10:06:16'),(1662,9,70,1,NULL,NULL,NULL,'2026-03-02 10:06:17','2026-03-02 10:06:17'),(1663,9,71,1,NULL,NULL,NULL,'2026-03-02 10:06:18','2026-03-02 10:06:18'),(1664,9,73,1,NULL,NULL,NULL,'2026-03-02 10:06:19','2026-03-02 10:06:19'),(1665,9,74,1,NULL,NULL,NULL,'2026-03-02 10:06:20','2026-03-02 10:06:20'),(1666,9,135,1,NULL,NULL,NULL,'2026-03-02 10:06:21','2026-03-02 10:06:21'),(1667,9,272,1,NULL,NULL,NULL,'2026-03-02 10:06:23','2026-03-02 10:06:23'),(1668,9,283,1,NULL,NULL,NULL,'2026-03-02 10:06:24','2026-03-02 10:06:24'),(1669,9,284,1,NULL,NULL,NULL,'2026-03-02 10:06:25','2026-03-02 10:06:25'),(1670,9,285,1,NULL,NULL,NULL,'2026-03-02 10:06:26','2026-03-02 10:06:26'),(1671,9,286,1,NULL,NULL,NULL,'2026-03-02 10:06:26','2026-03-02 10:06:26'),(1672,9,9,1,NULL,NULL,NULL,'2026-03-02 10:06:27','2026-03-02 10:06:27'),(1673,9,10,1,NULL,NULL,NULL,'2026-03-02 10:06:28','2026-03-02 10:06:28'),(1674,9,11,1,NULL,NULL,NULL,'2026-03-02 10:06:29','2026-03-02 10:06:29'),(1675,9,12,1,NULL,NULL,NULL,'2026-03-02 10:06:32','2026-03-02 10:06:32'),(1676,9,13,1,NULL,NULL,NULL,'2026-03-02 10:06:32','2026-03-02 10:06:32'),(1677,9,14,1,NULL,NULL,NULL,'2026-03-02 10:06:33','2026-03-02 10:06:33'),(1678,9,15,1,NULL,NULL,NULL,'2026-03-02 10:06:34','2026-03-02 10:06:34'),(1679,9,122,1,NULL,NULL,NULL,'2026-03-02 10:06:35','2026-03-02 10:06:35'),(1680,9,136,1,NULL,NULL,NULL,'2026-03-02 10:06:36','2026-03-02 10:06:36'),(1681,9,20,1,NULL,NULL,NULL,'2026-03-02 10:06:37','2026-03-02 10:06:37'),(1682,9,137,1,NULL,NULL,NULL,'2026-03-02 10:06:38','2026-03-02 10:06:38'),(1683,9,141,1,NULL,NULL,NULL,'2026-03-02 10:06:39','2026-03-02 10:06:39'),(1684,9,142,1,NULL,NULL,NULL,'2026-03-02 10:06:40','2026-03-02 10:06:40'),(1685,9,143,1,NULL,NULL,NULL,'2026-03-02 10:06:41','2026-03-02 10:06:41'),(1686,9,144,1,NULL,NULL,NULL,'2026-03-02 10:06:42','2026-03-02 10:06:42'),(1687,9,187,1,NULL,NULL,NULL,'2026-03-02 10:06:43','2026-03-02 10:06:43'),(1688,9,196,1,NULL,NULL,NULL,'2026-03-02 10:06:43','2026-03-02 10:06:43'),(1689,9,205,1,NULL,NULL,NULL,'2026-03-02 10:06:44','2026-03-02 10:06:44'),(1690,9,207,1,NULL,NULL,NULL,'2026-03-02 10:06:47','2026-03-02 10:06:47'),(1691,9,208,1,NULL,NULL,NULL,'2026-03-02 10:06:47','2026-03-02 10:06:47'),(1692,9,210,1,NULL,NULL,NULL,'2026-03-02 10:06:48','2026-03-02 10:06:48'),(1693,9,211,1,NULL,NULL,NULL,'2026-03-02 10:06:49','2026-03-02 10:06:49'),(1694,9,212,1,NULL,NULL,NULL,'2026-03-02 10:06:49','2026-03-02 10:06:49'),(1695,9,251,1,NULL,NULL,NULL,'2026-03-02 10:06:50','2026-03-02 10:06:50'),(1696,9,266,1,NULL,NULL,NULL,'2026-03-02 10:06:51','2026-03-02 10:06:51'),(1697,9,267,1,NULL,NULL,NULL,'2026-03-02 10:06:51','2026-03-02 10:06:51'),(1698,9,21,1,NULL,NULL,NULL,'2026-03-02 10:06:52','2026-03-02 10:06:52'),(1699,9,23,1,NULL,NULL,NULL,'2026-03-02 10:06:54','2026-03-02 10:06:54'),(1700,9,24,1,NULL,NULL,NULL,'2026-03-02 10:06:55','2026-03-02 10:06:55'),(1701,9,25,1,NULL,NULL,NULL,'2026-03-02 10:06:56','2026-03-02 10:06:56'),(1702,9,26,1,NULL,NULL,NULL,'2026-03-02 10:06:57','2026-03-02 10:06:57'),(1703,9,77,1,NULL,NULL,NULL,'2026-03-02 10:06:57','2026-03-02 10:06:57'),(1704,9,145,1,NULL,NULL,NULL,'2026-03-02 10:06:58','2026-03-02 10:06:58'),(1705,9,188,1,NULL,NULL,NULL,'2026-03-02 10:06:59','2026-03-02 10:06:59'),(1706,9,27,1,NULL,NULL,NULL,'2026-03-02 10:07:00','2026-03-02 10:07:00'),(1707,9,269,1,NULL,NULL,NULL,'2026-03-02 10:07:01','2026-03-02 10:07:01'),(1708,9,270,1,NULL,NULL,NULL,'2026-03-02 10:07:02','2026-03-02 10:07:02'),(1709,9,268,1,NULL,NULL,NULL,'2026-03-02 10:07:03','2026-03-02 10:07:03'),(1710,9,10008,1,NULL,NULL,NULL,'2026-03-02 10:07:04','2026-03-02 10:07:04'),(1711,9,10009,1,NULL,NULL,NULL,'2026-03-02 10:07:05','2026-03-02 10:07:05'),(1712,9,10010,1,NULL,NULL,NULL,'2026-03-02 10:07:06','2026-03-02 10:07:06'),(1713,9,10011,1,NULL,NULL,NULL,'2026-03-02 10:07:07','2026-03-02 10:07:07'),(1714,9,10012,1,NULL,NULL,NULL,'2026-03-02 10:07:08','2026-03-02 10:07:08'),(1715,9,10013,1,NULL,NULL,NULL,'2026-03-02 10:07:09','2026-03-02 10:07:09'),(1716,9,10014,1,NULL,NULL,NULL,'2026-03-02 10:07:09','2026-03-02 10:07:09'),(1717,9,10015,1,NULL,NULL,NULL,'2026-03-02 10:07:11','2026-03-02 10:07:11'),(1718,9,10016,1,NULL,NULL,NULL,'2026-03-02 10:07:11','2026-03-02 10:07:11'),(1719,9,10017,1,NULL,NULL,NULL,'2026-03-02 10:07:12','2026-03-02 10:07:12'),(1720,9,10019,1,NULL,NULL,NULL,'2026-03-02 10:07:13','2026-03-02 10:07:13'),(1721,9,10018,1,NULL,NULL,NULL,'2026-03-02 10:07:15','2026-03-02 10:07:15'),(1722,9,10020,1,NULL,NULL,NULL,'2026-03-02 10:07:16','2026-03-02 10:07:16'),(1723,9,10021,1,NULL,NULL,NULL,'2026-03-02 10:07:17','2026-03-02 10:07:17'),(1724,9,10022,1,NULL,NULL,NULL,'2026-03-02 10:07:17','2026-03-02 10:07:17'),(1725,9,31,1,NULL,NULL,NULL,'2026-03-02 10:07:18','2026-03-02 10:07:18'),(1726,9,32,1,NULL,NULL,NULL,'2026-03-02 10:07:19','2026-03-02 10:07:19'),(1727,9,33,1,NULL,NULL,NULL,'2026-03-02 10:07:21','2026-03-02 10:07:21'),(1728,9,34,1,NULL,NULL,NULL,'2026-03-02 10:07:22','2026-03-02 10:07:22'),(1729,9,35,1,NULL,NULL,NULL,'2026-03-02 10:07:22','2026-03-02 10:07:22'),(1730,9,104,1,NULL,NULL,NULL,'2026-03-02 10:07:23','2026-03-02 10:07:23'),(1731,9,37,1,NULL,NULL,NULL,'2026-03-02 10:07:23','2026-03-02 10:07:23'),(1732,9,38,1,NULL,NULL,NULL,'2026-03-02 10:07:24','2026-03-02 10:07:24'),(1733,9,39,1,NULL,NULL,NULL,'2026-03-02 10:07:25','2026-03-02 10:07:25'),(1734,9,259,1,NULL,NULL,NULL,'2026-03-02 10:07:26','2026-03-02 10:07:26'),(1735,9,260,1,NULL,NULL,NULL,'2026-03-02 10:07:26','2026-03-02 10:07:26'),(1736,9,261,1,NULL,NULL,NULL,'2026-03-02 10:07:29','2026-03-02 10:07:29'),(1737,9,262,1,NULL,NULL,NULL,'2026-03-02 10:07:30','2026-03-02 10:07:30'),(1738,9,40,1,NULL,NULL,NULL,'2026-03-02 10:07:31','2026-03-02 10:07:31'),(1739,9,41,1,NULL,NULL,NULL,'2026-03-02 10:07:32','2026-03-02 10:07:32'),(1740,9,42,1,NULL,NULL,NULL,'2026-03-02 10:07:33','2026-03-02 10:07:33'),(1741,9,43,1,NULL,NULL,NULL,'2026-03-02 10:07:33','2026-03-02 10:07:33'),(1742,9,44,1,NULL,NULL,NULL,'2026-03-02 10:07:34','2026-03-02 10:07:34'),(1743,9,217,1,NULL,NULL,NULL,'2026-03-02 10:07:35','2026-03-02 10:07:35'),(1744,9,46,1,NULL,NULL,NULL,'2026-03-02 10:07:36','2026-03-02 10:07:36'),(1745,9,252,1,NULL,NULL,NULL,'2026-03-02 10:07:37','2026-03-02 10:07:37'),(1746,9,253,1,NULL,NULL,NULL,'2026-03-02 10:07:40','2026-03-02 10:07:40'),(1747,9,254,1,NULL,NULL,NULL,'2026-03-02 10:07:41','2026-03-02 10:07:41'),(1748,9,255,1,NULL,NULL,NULL,'2026-03-02 10:07:42','2026-03-02 10:07:42'),(1749,9,146,1,NULL,NULL,NULL,'2026-03-02 10:07:42','2026-03-02 10:07:42'),(1750,9,147,1,NULL,NULL,NULL,'2026-03-02 10:07:43','2026-03-02 10:07:43'),(1751,9,148,1,NULL,NULL,NULL,'2026-03-02 10:07:44','2026-03-02 10:07:44'),(1752,9,149,1,NULL,NULL,NULL,'2026-03-02 10:07:45','2026-03-02 10:07:45'),(1753,9,150,1,NULL,NULL,NULL,'2026-03-02 10:07:47','2026-03-02 10:07:47'),(1754,9,151,1,NULL,NULL,NULL,'2026-03-02 10:07:48','2026-03-02 10:07:48'),(1755,9,152,1,NULL,NULL,NULL,'2026-03-02 10:07:49','2026-03-02 10:07:49'),(1756,9,153,1,NULL,NULL,NULL,'2026-03-02 10:07:50','2026-03-02 10:07:50'),(1757,9,154,1,NULL,NULL,NULL,'2026-03-02 10:07:50','2026-03-02 10:07:50'),(1758,9,155,1,NULL,NULL,NULL,'2026-03-02 10:07:51','2026-03-02 10:07:51'),(1759,9,156,1,NULL,NULL,NULL,'2026-03-02 10:07:53','2026-03-02 10:07:53'),(1760,9,157,1,NULL,NULL,NULL,'2026-03-02 10:07:54','2026-03-02 10:07:54'),(1761,9,158,1,NULL,NULL,NULL,'2026-03-02 10:07:54','2026-03-02 10:07:54'),(1762,9,159,1,NULL,NULL,NULL,'2026-03-02 10:07:57','2026-03-02 10:07:57'),(1763,9,160,1,NULL,NULL,NULL,'2026-03-02 10:07:58','2026-03-02 10:07:58'),(1764,9,161,1,NULL,NULL,NULL,'2026-03-02 10:07:59','2026-03-02 10:07:59'),(1765,9,162,1,NULL,NULL,NULL,'2026-03-02 10:08:00','2026-03-02 10:08:00'),(1766,9,163,1,NULL,NULL,NULL,'2026-03-02 10:08:01','2026-03-02 10:08:01'),(1767,9,164,1,NULL,NULL,NULL,'2026-03-02 10:08:02','2026-03-02 10:08:02'),(1768,9,165,1,NULL,NULL,NULL,'2026-03-02 10:08:03','2026-03-02 10:08:03'),(1769,9,174,1,NULL,NULL,NULL,'2026-03-02 10:08:04','2026-03-02 10:08:04'),(1770,9,175,1,NULL,NULL,NULL,'2026-03-02 10:08:05','2026-03-02 10:08:05'),(1771,9,176,1,NULL,NULL,NULL,'2026-03-02 10:08:05','2026-03-02 10:08:05'),(1772,9,177,1,NULL,NULL,NULL,'2026-03-02 10:08:06','2026-03-02 10:08:06'),(1773,9,178,1,NULL,NULL,NULL,'2026-03-02 10:08:07','2026-03-02 10:08:07'),(1774,9,179,1,NULL,NULL,NULL,'2026-03-02 10:08:08','2026-03-02 10:08:08'),(1775,9,180,1,NULL,NULL,NULL,'2026-03-02 10:08:09','2026-03-02 10:08:09'),(1776,9,181,1,NULL,NULL,NULL,'2026-03-02 10:08:10','2026-03-02 10:08:10'),(1777,9,182,1,NULL,NULL,NULL,'2026-03-02 10:08:10','2026-03-02 10:08:10'),(1778,9,183,1,NULL,NULL,NULL,'2026-03-02 10:08:11','2026-03-02 10:08:11'),(1779,9,197,1,NULL,NULL,NULL,'2026-03-02 10:08:14','2026-03-02 10:08:14'),(1780,9,198,1,NULL,NULL,NULL,'2026-03-02 10:08:14','2026-03-02 10:08:14'),(1781,9,200,1,NULL,NULL,NULL,'2026-03-02 10:08:16','2026-03-02 10:08:16'),(1782,9,201,1,NULL,NULL,NULL,'2026-03-02 10:08:17','2026-03-02 10:08:17'),(1783,9,202,1,NULL,NULL,NULL,'2026-03-02 10:08:18','2026-03-02 10:08:18'),(1784,9,203,1,NULL,NULL,NULL,'2026-03-02 10:08:18','2026-03-02 10:08:18'),(1785,9,204,1,NULL,NULL,NULL,'2026-03-02 10:08:19','2026-03-02 10:08:19'),(1786,9,219,1,NULL,NULL,NULL,'2026-03-02 10:08:20','2026-03-02 10:08:20'),(1787,9,220,1,NULL,NULL,NULL,'2026-03-02 10:08:20','2026-03-02 10:08:20'),(1788,9,221,1,NULL,NULL,NULL,'2026-03-02 10:08:21','2026-03-02 10:08:21'),(1789,9,223,1,NULL,NULL,NULL,'2026-03-02 10:08:22','2026-03-02 10:08:22'),(1790,9,240,1,NULL,NULL,NULL,'2026-03-02 10:08:23','2026-03-02 10:08:23'),(1791,9,241,1,NULL,NULL,NULL,'2026-03-02 10:08:23','2026-03-02 10:08:23'),(1792,9,242,1,NULL,NULL,NULL,'2026-03-02 10:08:24','2026-03-02 10:08:24'),(1793,9,244,1,NULL,NULL,NULL,'2026-03-02 10:08:25','2026-03-02 10:08:25'),(1794,9,245,1,NULL,NULL,NULL,'2026-03-02 10:08:26','2026-03-02 10:08:26'),(1795,9,246,1,NULL,NULL,NULL,'2026-03-02 10:08:27','2026-03-02 10:08:27'),(1796,9,256,1,NULL,NULL,NULL,'2026-03-02 10:08:29','2026-03-02 10:08:29'),(1797,9,257,1,NULL,NULL,NULL,'2026-03-02 10:08:30','2026-03-02 10:08:30'),(1798,9,258,1,NULL,NULL,NULL,'2026-03-02 10:08:31','2026-03-02 10:08:31'),(1799,9,276,1,NULL,NULL,NULL,'2026-03-02 10:08:32','2026-03-02 10:08:32'),(1800,9,80,1,NULL,NULL,NULL,'2026-03-02 10:08:41','2026-03-02 10:08:41'),(1801,9,81,1,NULL,NULL,NULL,'2026-03-02 10:08:42','2026-03-02 10:08:42'),(1802,9,82,1,NULL,NULL,NULL,'2026-03-02 10:08:43','2026-03-02 10:08:43'),(1803,9,83,1,NULL,NULL,NULL,'2026-03-02 10:08:43','2026-03-02 10:08:43'),(1804,9,84,1,NULL,NULL,NULL,'2026-03-02 10:08:44','2026-03-02 10:08:44'),(1805,9,85,1,NULL,NULL,NULL,'2026-03-02 10:08:45','2026-03-02 10:08:45'),(1806,9,86,1,NULL,NULL,NULL,'2026-03-02 10:08:46','2026-03-02 10:08:46'),(1807,9,87,1,NULL,NULL,NULL,'2026-03-02 10:08:49','2026-03-02 10:08:49'),(1808,9,88,1,NULL,NULL,NULL,'2026-03-02 10:08:50','2026-03-02 10:08:50'),(1809,9,90,1,NULL,NULL,NULL,'2026-03-02 10:08:51','2026-03-02 10:08:51'),(1810,9,108,1,NULL,NULL,NULL,'2026-03-02 10:08:52','2026-03-02 10:08:52'),(1811,9,109,1,1,NULL,NULL,'2026-03-02 10:08:52','2026-03-16 06:45:03'),(1812,9,110,1,NULL,NULL,NULL,'2026-03-02 10:08:53','2026-03-02 10:08:53'),(1813,9,111,1,NULL,NULL,NULL,'2026-03-02 10:08:54','2026-03-02 10:08:54'),(1814,9,112,1,NULL,NULL,NULL,'2026-03-02 10:08:54','2026-03-02 10:08:54'),(1815,9,127,1,NULL,NULL,NULL,'2026-03-02 10:08:55','2026-03-02 10:08:55'),(1816,9,129,1,NULL,NULL,NULL,'2026-03-02 10:08:56','2026-03-02 10:08:56'),(1817,9,189,1,NULL,NULL,NULL,'2026-03-02 10:08:56','2026-03-02 10:08:56'),(1818,9,10025,1,NULL,NULL,NULL,'2026-03-02 10:08:57','2026-03-02 10:08:57'),(1819,9,10026,1,NULL,NULL,NULL,'2026-03-02 10:08:58','2026-03-02 10:08:58'),(1820,9,10027,1,NULL,NULL,NULL,'2026-03-02 10:08:59','2026-03-02 10:08:59'),(1821,9,93,1,NULL,NULL,NULL,'2026-03-02 10:08:59','2026-03-02 10:08:59'),(1822,9,94,1,NULL,NULL,NULL,'2026-03-02 10:09:03','2026-03-02 10:09:03'),(1823,9,250,1,NULL,NULL,NULL,'2026-03-02 10:09:04','2026-03-02 10:09:04'),(1824,9,97,1,NULL,NULL,NULL,'2026-03-02 10:09:05','2026-03-02 10:09:05'),(1825,9,96,1,NULL,NULL,NULL,'2026-03-02 10:09:07','2026-03-02 10:09:07'),(1826,9,98,1,NULL,NULL,NULL,'2026-03-02 10:09:08','2026-03-02 10:09:08'),(1827,9,99,1,NULL,NULL,NULL,'2026-03-02 10:09:09','2026-03-02 10:09:09'),(1828,9,248,1,NULL,NULL,NULL,'2026-03-02 10:09:09','2026-03-02 10:09:09'),(1829,9,249,1,NULL,NULL,NULL,'2026-03-02 10:09:10','2026-03-02 10:09:10'),(1830,9,185,1,NULL,NULL,NULL,'2026-03-02 10:09:14','2026-03-02 10:09:14'),(1831,9,186,1,NULL,NULL,NULL,'2026-03-02 10:09:14','2026-03-02 10:09:14'),(1832,9,214,1,NULL,NULL,NULL,'2026-03-02 10:09:15','2026-03-02 10:09:15'),(1833,9,222,1,NULL,NULL,NULL,'2026-03-02 10:09:17','2026-03-02 10:09:17'),(1834,9,247,1,NULL,NULL,NULL,'2026-03-02 10:09:18','2026-03-02 10:09:18'),(1835,9,231,1,NULL,NULL,NULL,'2026-03-02 10:09:19','2026-03-02 10:09:19'),(1836,9,224,1,NULL,NULL,NULL,'2026-03-02 10:09:20','2026-03-02 10:09:20'),(1837,9,232,1,NULL,NULL,NULL,'2026-03-02 10:09:21','2026-03-02 10:09:21'),(1838,9,234,1,NULL,NULL,NULL,'2026-03-02 10:09:23','2026-03-02 10:09:23'),(1839,9,235,1,NULL,NULL,NULL,'2026-03-02 10:09:24','2026-03-02 10:09:24'),(1840,9,236,1,NULL,NULL,NULL,'2026-03-02 10:09:24','2026-03-02 10:09:24'),(1841,9,237,1,NULL,NULL,NULL,'2026-03-02 10:09:25','2026-03-02 10:09:25'),(1842,9,238,1,NULL,NULL,NULL,'2026-03-02 10:09:26','2026-03-02 10:09:26'),(1843,9,239,1,NULL,NULL,NULL,'2026-03-02 10:09:26','2026-03-02 10:09:26'),(1844,9,263,1,NULL,NULL,NULL,'2026-03-02 10:09:27','2026-03-02 10:09:27'),(1845,9,273,1,NULL,NULL,NULL,'2026-03-02 10:09:28','2026-03-02 10:09:28'),(1846,9,275,1,NULL,NULL,NULL,'2026-03-02 10:09:30','2026-03-02 10:09:30'),(1847,9,277,1,NULL,NULL,NULL,'2026-03-02 10:09:31','2026-03-02 10:09:31'),(1848,9,278,1,NULL,NULL,NULL,'2026-03-02 10:09:32','2026-03-02 10:09:32'),(1849,9,279,1,NULL,NULL,NULL,'2026-03-02 10:09:33','2026-03-02 10:09:33'),(1850,9,10001,1,NULL,NULL,NULL,'2026-03-02 10:09:33','2026-03-02 10:09:33'),(1851,9,10002,1,NULL,NULL,NULL,'2026-03-02 10:09:34','2026-03-02 10:09:34'),(1852,9,10003,1,NULL,NULL,NULL,'2026-03-02 10:09:35','2026-03-02 10:09:35'),(1853,9,10004,1,NULL,NULL,NULL,'2026-03-02 10:09:35','2026-03-02 10:09:35'),(1854,9,10005,1,NULL,NULL,NULL,'2026-03-02 10:09:36','2026-03-02 10:09:36'),(1855,9,10006,1,NULL,NULL,NULL,'2026-03-02 10:09:37','2026-03-02 10:09:37'),(1856,9,10007,1,NULL,NULL,NULL,'2026-03-02 10:09:37','2026-03-02 10:09:37'),(1857,10,1,1,NULL,NULL,NULL,'2026-03-02 10:10:25','2026-03-02 10:10:25'),(1858,10,2,1,NULL,NULL,NULL,'2026-03-02 10:10:25','2026-03-02 10:10:25'),(1859,10,3,1,NULL,NULL,NULL,'2026-03-02 10:10:26','2026-03-02 10:10:26'),(1860,10,4,1,NULL,NULL,NULL,'2026-03-02 10:10:27','2026-03-02 10:10:27'),(1861,10,107,1,NULL,NULL,NULL,'2026-03-02 10:10:28','2026-03-02 10:10:28'),(1862,10,128,1,NULL,NULL,NULL,'2026-03-02 10:10:29','2026-03-02 10:10:29'),(1863,10,134,1,NULL,NULL,NULL,'2026-03-02 10:10:29','2026-03-02 10:10:29'),(1864,10,9,1,NULL,NULL,NULL,'2026-03-02 10:10:38','2026-03-02 10:10:38'),(1865,10,10,1,NULL,NULL,NULL,'2026-03-02 10:10:39','2026-03-02 10:10:39'),(1866,10,11,1,NULL,NULL,NULL,'2026-03-02 10:10:39','2026-03-02 10:10:39'),(1867,10,12,1,NULL,NULL,NULL,'2026-03-02 10:10:40','2026-03-02 10:10:40'),(1868,10,13,1,NULL,NULL,NULL,'2026-03-02 10:10:42','2026-03-02 10:10:42'),(1869,10,14,1,NULL,NULL,NULL,'2026-03-02 10:10:44','2026-03-02 10:10:44'),(1870,10,15,1,NULL,NULL,NULL,'2026-03-02 10:10:46','2026-03-02 10:10:46'),(1871,10,136,1,1,1,NULL,'2026-03-02 10:10:47','2026-03-16 06:30:49'),(1872,10,122,1,NULL,NULL,NULL,'2026-03-02 10:10:48','2026-03-02 10:10:48'),(1873,10,20,1,NULL,NULL,NULL,'2026-03-02 10:10:49','2026-03-02 10:10:49'),(1874,10,137,1,NULL,NULL,NULL,'2026-03-02 10:10:50','2026-03-02 10:10:50'),(1875,10,141,1,NULL,NULL,NULL,'2026-03-02 10:10:51','2026-03-02 10:10:51'),(1876,10,143,1,NULL,NULL,NULL,'2026-03-02 10:10:53','2026-03-02 10:10:53'),(1877,10,142,1,NULL,NULL,NULL,'2026-03-02 10:10:54','2026-03-02 10:10:54'),(1878,10,144,1,NULL,NULL,NULL,'2026-03-02 10:10:56','2026-03-02 10:10:56'),(1879,10,187,1,NULL,NULL,NULL,'2026-03-02 10:10:56','2026-03-02 10:10:56'),(1880,10,196,1,NULL,NULL,NULL,'2026-03-02 10:10:57','2026-03-02 10:10:57'),(1881,10,205,1,NULL,NULL,NULL,'2026-03-02 10:10:59','2026-03-02 10:10:59'),(1882,10,207,1,NULL,NULL,NULL,'2026-03-02 10:10:59','2026-03-02 10:10:59'),(1883,10,208,1,NULL,NULL,NULL,'2026-03-02 10:11:00','2026-03-02 10:11:00'),(1884,10,210,1,NULL,NULL,NULL,'2026-03-02 10:11:01','2026-03-02 10:11:01'),(1885,10,211,1,NULL,NULL,NULL,'2026-03-02 10:11:02','2026-03-02 10:11:02'),(1886,10,212,1,NULL,NULL,NULL,'2026-03-02 10:11:03','2026-03-02 10:11:03'),(1887,10,251,1,NULL,NULL,NULL,'2026-03-02 10:11:05','2026-03-02 10:11:05'),(1888,10,266,1,NULL,NULL,NULL,'2026-03-02 10:11:06','2026-03-02 10:11:06'),(1889,10,267,1,NULL,NULL,NULL,'2026-03-02 10:11:07','2026-03-02 10:11:07'),(1890,10,21,1,NULL,NULL,NULL,'2026-03-02 10:11:07','2026-03-02 10:11:07'),(1891,10,23,1,NULL,NULL,NULL,'2026-03-02 10:11:08','2026-03-02 10:11:08'),(1892,10,24,1,NULL,NULL,NULL,'2026-03-02 10:11:09','2026-03-02 10:11:09'),(1893,10,25,1,NULL,NULL,NULL,'2026-03-02 10:11:10','2026-03-02 10:11:10'),(1894,10,26,1,NULL,NULL,NULL,'2026-03-02 10:11:11','2026-03-02 10:11:11'),(1895,10,77,1,NULL,NULL,NULL,'2026-03-02 10:11:12','2026-03-02 10:11:12'),(1896,10,145,1,NULL,NULL,NULL,'2026-03-02 10:11:12','2026-03-02 10:11:12'),(1897,10,188,1,NULL,NULL,NULL,'2026-03-02 10:11:13','2026-03-02 10:11:13'),(1898,10,27,1,NULL,NULL,NULL,'2026-03-02 10:11:14','2026-03-02 10:11:14'),(1899,10,268,1,NULL,NULL,NULL,'2026-03-02 10:11:15','2026-03-02 10:11:15'),(1900,10,269,1,NULL,NULL,NULL,'2026-03-02 10:11:19','2026-03-02 10:11:19'),(1901,10,270,1,NULL,NULL,NULL,'2026-03-02 10:11:19','2026-03-02 10:11:19'),(1902,10,10008,1,NULL,NULL,NULL,'2026-03-02 10:11:20','2026-03-02 10:11:20'),(1903,10,10009,1,NULL,NULL,NULL,'2026-03-02 10:11:21','2026-03-02 10:11:21'),(1904,10,10010,1,NULL,NULL,NULL,'2026-03-02 10:11:22','2026-03-02 10:11:22'),(1905,10,10011,1,NULL,NULL,NULL,'2026-03-02 10:11:22','2026-03-02 10:11:22'),(1906,10,10012,1,NULL,NULL,NULL,'2026-03-02 10:11:23','2026-03-02 10:11:23'),(1907,10,10013,1,NULL,NULL,NULL,'2026-03-02 10:11:24','2026-03-02 10:11:24'),(1908,10,10014,1,NULL,NULL,NULL,'2026-03-02 10:11:24','2026-03-02 10:11:24'),(1909,10,10015,1,NULL,NULL,NULL,'2026-03-02 10:11:25','2026-03-02 10:11:25'),(1910,10,10016,1,NULL,NULL,NULL,'2026-03-02 10:11:30','2026-03-02 10:11:30'),(1911,10,10017,1,NULL,NULL,NULL,'2026-03-02 10:11:32','2026-03-02 10:11:32'),(1912,10,10018,1,NULL,NULL,NULL,'2026-03-02 10:11:33','2026-03-02 10:11:33'),(1913,10,10019,1,NULL,NULL,NULL,'2026-03-02 10:11:33','2026-03-02 10:11:33'),(1914,10,10020,1,NULL,NULL,NULL,'2026-03-02 10:11:35','2026-03-02 10:11:35'),(1915,10,10021,1,NULL,NULL,NULL,'2026-03-02 10:11:36','2026-03-02 10:11:36'),(1916,10,10022,1,NULL,NULL,NULL,'2026-03-02 10:11:38','2026-03-02 10:11:38'),(1917,10,31,1,NULL,NULL,NULL,'2026-03-02 10:11:39','2026-03-02 10:11:39'),(1918,10,32,1,NULL,NULL,NULL,'2026-03-02 10:11:40','2026-03-02 10:11:40'),(1919,10,33,1,NULL,NULL,NULL,'2026-03-02 10:11:41','2026-03-02 10:11:41'),(1920,10,35,1,NULL,NULL,NULL,'2026-03-02 10:11:42','2026-03-02 10:11:42'),(1921,10,34,1,NULL,NULL,NULL,'2026-03-02 10:11:43','2026-03-02 10:11:43'),(1922,10,104,1,NULL,NULL,NULL,'2026-03-02 10:11:43','2026-03-02 10:11:43'),(1923,10,37,1,NULL,NULL,NULL,'2026-03-02 10:11:44','2026-03-02 10:11:44'),(1924,10,39,1,NULL,NULL,NULL,'2026-03-02 10:11:46','2026-03-02 10:11:46'),(1925,10,38,1,NULL,NULL,NULL,'2026-03-02 10:11:47','2026-03-02 10:11:47'),(1926,10,259,1,NULL,NULL,NULL,'2026-03-02 10:11:50','2026-03-02 10:11:50'),(1927,10,260,1,NULL,NULL,NULL,'2026-03-02 10:11:50','2026-03-02 10:11:50'),(1928,10,261,1,NULL,NULL,NULL,'2026-03-02 10:11:51','2026-03-02 10:11:51'),(1929,10,262,1,NULL,NULL,NULL,'2026-03-02 10:11:52','2026-03-02 10:11:52'),(1930,10,41,1,NULL,NULL,NULL,'2026-03-02 10:11:53','2026-03-02 10:11:53'),(1931,10,40,1,NULL,NULL,NULL,'2026-03-02 10:11:54','2026-03-02 10:11:54'),(1932,10,42,1,NULL,NULL,NULL,'2026-03-02 10:11:55','2026-03-02 10:11:55'),(1933,10,43,1,NULL,NULL,NULL,'2026-03-02 10:11:56','2026-03-02 10:11:56'),(1934,10,44,1,NULL,NULL,NULL,'2026-03-02 10:11:57','2026-03-02 10:11:57'),(1935,10,46,1,NULL,NULL,NULL,'2026-03-02 10:11:58','2026-03-02 10:11:58'),(1936,10,217,1,NULL,NULL,NULL,'2026-03-02 10:11:59','2026-03-02 10:11:59'),(1937,10,252,1,NULL,NULL,NULL,'2026-03-02 10:11:59','2026-03-02 10:11:59'),(1938,10,253,1,NULL,NULL,NULL,'2026-03-02 10:12:00','2026-03-02 10:12:00'),(1939,10,254,1,NULL,NULL,NULL,'2026-03-02 10:12:01','2026-03-02 10:12:01'),(1940,10,255,1,NULL,NULL,NULL,'2026-03-02 10:12:03','2026-03-02 10:12:03'),(1941,10,146,1,NULL,NULL,NULL,'2026-03-02 10:12:04','2026-03-02 10:12:04'),(1942,10,147,1,NULL,NULL,NULL,'2026-03-02 10:12:05','2026-03-02 10:12:05'),(1943,10,148,1,NULL,NULL,NULL,'2026-03-02 10:12:07','2026-03-02 10:12:07'),(1944,10,78,1,NULL,NULL,NULL,'2026-03-02 10:12:25','2026-03-02 10:12:25'),(1945,10,79,1,NULL,NULL,NULL,'2026-03-02 10:12:26','2026-03-02 10:12:26'),(1946,10,80,1,NULL,NULL,NULL,'2026-03-02 10:12:26','2026-03-02 10:12:26'),(1947,10,81,1,NULL,NULL,NULL,'2026-03-02 10:12:28','2026-03-02 10:12:28'),(1948,10,82,1,NULL,NULL,NULL,'2026-03-02 10:12:28','2026-03-02 10:12:28'),(1949,10,83,1,NULL,NULL,NULL,'2026-03-02 10:12:29','2026-03-02 10:12:29'),(1950,10,84,1,NULL,NULL,NULL,'2026-03-02 10:12:30','2026-03-02 10:12:30'),(1951,10,85,1,NULL,NULL,NULL,'2026-03-02 10:12:31','2026-03-02 10:12:31'),(1952,10,86,1,NULL,NULL,NULL,'2026-03-02 10:12:32','2026-03-02 10:12:32'),(1953,10,87,1,NULL,NULL,NULL,'2026-03-02 10:12:36','2026-03-02 10:12:36'),(1954,10,88,1,NULL,NULL,NULL,'2026-03-02 10:12:37','2026-03-02 10:12:37'),(1955,10,90,1,NULL,NULL,NULL,'2026-03-02 10:12:38','2026-03-02 10:12:38'),(1956,10,108,1,NULL,1,NULL,'2026-03-02 10:12:38','2026-03-16 06:30:59'),(1957,10,109,1,1,NULL,NULL,'2026-03-02 10:12:39','2026-03-16 06:45:03'),(1958,10,110,1,NULL,NULL,NULL,'2026-03-02 10:12:40','2026-03-02 10:12:40'),(1959,10,111,1,NULL,NULL,NULL,'2026-03-02 10:12:41','2026-03-02 10:12:41'),(1960,10,112,1,NULL,NULL,NULL,'2026-03-02 10:12:42','2026-03-02 10:12:42'),(1961,10,127,1,NULL,NULL,NULL,'2026-03-02 10:12:42','2026-03-02 10:12:42'),(1962,10,129,1,NULL,NULL,NULL,'2026-03-02 10:12:43','2026-03-02 10:12:43'),(1963,10,189,1,NULL,NULL,NULL,'2026-03-02 10:12:44','2026-03-02 10:12:44'),(1964,10,10025,1,NULL,NULL,NULL,'2026-03-02 10:12:45','2026-03-02 10:12:45'),(1965,10,10026,1,NULL,NULL,NULL,'2026-03-02 10:12:45','2026-03-02 10:12:45'),(1966,10,10027,1,NULL,NULL,NULL,'2026-03-02 10:12:46','2026-03-02 10:12:46'),(1967,10,93,1,NULL,NULL,NULL,'2026-03-02 10:12:47','2026-03-02 10:12:47'),(1968,10,94,1,NULL,NULL,NULL,'2026-03-02 10:12:48','2026-03-02 10:12:48'),(1969,10,250,1,NULL,NULL,NULL,'2026-03-02 10:12:51','2026-03-02 10:12:51'),(1970,10,96,1,NULL,NULL,NULL,'2026-03-02 10:12:51','2026-03-02 10:12:51'),(1971,10,97,1,NULL,NULL,NULL,'2026-03-02 10:12:52','2026-03-02 10:12:52'),(1972,10,98,1,NULL,NULL,NULL,'2026-03-02 10:12:53','2026-03-02 10:12:53'),(1973,10,99,1,NULL,NULL,NULL,'2026-03-02 10:12:53','2026-03-02 10:12:53'),(1974,10,248,1,NULL,NULL,NULL,'2026-03-02 10:12:54','2026-03-02 10:12:54'),(1975,10,249,1,NULL,NULL,NULL,'2026-03-02 10:12:55','2026-03-02 10:12:55'),(1976,10,106,1,NULL,NULL,NULL,'2026-03-02 10:12:56','2026-03-02 10:12:56'),(1977,10,102,1,NULL,NULL,NULL,'2026-03-02 10:12:58','2026-03-02 10:12:58'),(1978,10,113,1,NULL,NULL,NULL,'2026-03-02 10:12:59','2026-03-02 10:12:59'),(1979,10,114,1,NULL,NULL,NULL,'2026-03-02 10:13:05','2026-03-02 10:13:05'),(1980,10,115,1,NULL,NULL,NULL,'2026-03-02 10:13:06','2026-03-02 10:13:06'),(1981,10,116,1,NULL,NULL,NULL,'2026-03-02 10:13:08','2026-03-02 10:13:08'),(1982,10,117,1,NULL,NULL,NULL,'2026-03-02 10:13:09','2026-03-02 10:13:09'),(1983,10,118,1,NULL,NULL,NULL,'2026-03-02 10:13:10','2026-03-02 10:13:10'),(1984,10,190,1,NULL,NULL,NULL,'2026-03-02 10:13:11','2026-03-02 10:13:11'),(1985,10,191,1,NULL,NULL,NULL,'2026-03-02 10:13:12','2026-03-02 10:13:12'),(1986,10,192,1,NULL,NULL,NULL,'2026-03-02 10:13:13','2026-03-02 10:13:13'),(1987,10,193,1,NULL,NULL,NULL,'2026-03-02 10:13:14','2026-03-02 10:13:14'),(1988,10,194,1,NULL,NULL,NULL,'2026-03-02 10:13:15','2026-03-02 10:13:15'),(1989,10,195,1,NULL,NULL,NULL,'2026-03-02 10:13:15','2026-03-02 10:13:15'),(1990,10,226,1,NULL,NULL,NULL,'2026-03-02 10:13:16','2026-03-02 10:13:16'),(1991,10,227,1,NULL,NULL,NULL,'2026-03-02 10:13:17','2026-03-02 10:13:17'),(1992,10,229,1,NULL,NULL,NULL,'2026-03-02 10:13:18','2026-03-02 10:13:18'),(1993,10,230,1,NULL,NULL,NULL,'2026-03-02 10:13:18','2026-03-02 10:13:18'),(1994,10,280,1,NULL,NULL,NULL,'2026-03-02 10:13:19','2026-03-02 10:13:19'),(1995,10,281,1,NULL,NULL,NULL,'2026-03-02 10:13:20','2026-03-02 10:13:20'),(1996,10,282,1,NULL,NULL,NULL,'2026-03-02 10:13:21','2026-03-02 10:13:21'),(1997,10,10029,1,NULL,NULL,NULL,'2026-03-02 10:13:21','2026-03-02 10:13:21'),(1998,10,185,1,NULL,NULL,NULL,'2026-03-02 10:13:22','2026-03-02 10:13:22'),(1999,10,186,1,NULL,NULL,NULL,'2026-03-02 10:13:28','2026-03-02 10:13:28'),(2000,10,214,1,NULL,NULL,NULL,'2026-03-02 10:13:29','2026-03-02 10:13:29'),(2001,10,222,1,NULL,NULL,NULL,'2026-03-02 10:13:29','2026-03-02 10:13:29'),(2002,10,247,1,NULL,NULL,NULL,'2026-03-02 10:13:31','2026-03-02 10:13:31'),(2003,10,224,1,NULL,NULL,NULL,'2026-03-02 10:13:32','2026-03-02 10:13:32'),(2004,10,231,1,NULL,NULL,NULL,'2026-03-02 10:13:34','2026-03-02 10:13:34'),(2005,10,232,1,NULL,NULL,NULL,'2026-03-02 10:13:34','2026-03-02 10:13:34'),(2006,10,234,1,NULL,NULL,NULL,'2026-03-02 10:13:35','2026-03-02 10:13:35'),(2007,10,235,1,NULL,NULL,NULL,'2026-03-02 10:13:36','2026-03-02 10:13:36'),(2008,10,236,1,NULL,NULL,NULL,'2026-03-02 10:13:37','2026-03-02 10:13:37'),(2009,10,237,1,NULL,NULL,NULL,'2026-03-02 10:13:41','2026-03-02 10:13:41'),(2010,10,238,1,NULL,NULL,NULL,'2026-03-02 10:13:44','2026-03-02 10:13:44'),(2011,10,239,1,NULL,NULL,NULL,'2026-03-02 10:13:45','2026-03-02 10:13:45'),(2012,10,263,1,NULL,NULL,NULL,'2026-03-02 10:13:46','2026-03-02 10:13:46'),(2013,10,273,1,NULL,NULL,NULL,'2026-03-02 10:13:47','2026-03-02 10:13:47'),(2014,10,274,1,NULL,NULL,NULL,'2026-03-02 10:13:49','2026-03-02 10:13:49'),(2015,10,275,1,NULL,NULL,NULL,'2026-03-02 10:13:50','2026-03-02 10:13:50'),(2016,10,277,1,NULL,NULL,NULL,'2026-03-02 10:13:50','2026-03-02 10:13:50'),(2017,10,278,1,NULL,NULL,NULL,'2026-03-02 10:13:51','2026-03-02 10:13:51'),(2018,10,279,1,NULL,NULL,NULL,'2026-03-02 10:13:52','2026-03-02 10:13:52'),(2019,1,9001,1,1,1,1,'2026-03-05 21:44:21','2026-03-05 21:44:21'),(2020,1,9002,1,0,0,0,'2026-03-05 21:44:21','2026-03-05 21:44:21'),(2021,1,9003,1,0,1,0,'2026-03-05 21:44:21','2026-03-05 21:44:21'),(2022,1,9004,1,0,1,0,'2026-03-05 21:44:21','2026-03-05 21:44:21'),(2023,1,9005,1,0,1,0,'2026-03-05 21:44:21','2026-03-05 21:44:21'),(2024,1,9006,1,0,1,0,'2026-03-05 21:44:21','2026-03-05 21:44:21'),(2025,1,9007,1,0,1,0,'2026-03-05 21:44:21','2026-03-05 21:44:21'),(2026,1,9008,1,0,0,0,'2026-03-05 21:44:21','2026-03-05 21:44:21'),(2027,1,9009,1,1,1,1,'2026-03-05 21:44:21','2026-03-05 21:44:21'),(2028,1,9010,1,1,1,1,'2026-03-05 21:44:21','2026-03-05 21:44:21'),(2029,1,9011,1,1,1,1,'2026-03-05 21:44:21','2026-03-05 21:44:21'),(2030,1,9012,1,1,1,1,'2026-03-05 21:44:21','2026-03-05 21:44:21'),(2031,1,9013,1,1,1,1,'2026-03-05 21:44:21','2026-03-05 21:44:21'),(2032,1,9014,1,1,1,1,'2026-03-05 21:44:21','2026-03-05 21:44:21'),(2033,1,9015,1,1,1,1,'2026-03-05 21:44:21','2026-03-05 21:44:21'),(2034,1,9016,1,0,0,0,'2026-03-05 21:44:21','2026-03-05 21:44:21'),(2035,1,9017,1,0,0,0,'2026-03-05 21:44:21','2026-03-05 21:44:21'),(2036,1,9018,1,0,0,0,'2026-03-05 21:44:21','2026-03-05 21:44:21'),(2037,1,9019,1,0,0,0,'2026-03-05 21:44:21','2026-03-05 21:44:21'),(2038,1,9020,1,0,0,0,'2026-03-05 21:44:21','2026-03-05 21:44:21'),(2039,1,9021,1,1,1,1,'2026-03-05 21:44:21','2026-03-05 21:44:21'),(2040,1,9022,1,0,0,0,'2026-03-05 21:44:21','2026-03-05 21:44:21'),(2041,1,9023,1,1,1,1,'2026-03-05 21:44:21','2026-03-05 21:44:21'),(2042,7,9001,1,1,1,1,'2026-03-05 21:44:21','2026-03-05 21:44:21'),(2043,7,9002,1,0,0,0,'2026-03-05 21:44:21','2026-03-05 21:44:21'),(2044,7,9003,1,0,1,0,'2026-03-05 21:44:21','2026-03-05 21:44:21'),(2045,7,9004,1,0,1,0,'2026-03-05 21:44:21','2026-03-05 21:44:21'),(2046,7,9005,1,0,1,0,'2026-03-05 21:44:21','2026-03-05 21:44:21'),(2047,7,9006,1,0,1,0,'2026-03-05 21:44:21','2026-03-05 21:44:21'),(2048,7,9007,1,0,1,0,'2026-03-05 21:44:21','2026-03-05 21:44:21'),(2049,7,9008,1,0,0,0,'2026-03-05 21:44:21','2026-03-05 21:44:21'),(2050,7,9009,1,1,1,1,'2026-03-05 21:44:21','2026-03-05 21:44:21'),(2051,7,9010,1,1,1,1,'2026-03-05 21:44:21','2026-03-05 21:44:21'),(2052,7,9011,1,1,1,1,'2026-03-05 21:44:21','2026-03-05 21:44:21'),(2053,7,9012,1,1,1,1,'2026-03-05 21:44:21','2026-03-05 21:44:21'),(2054,7,9013,1,1,1,1,'2026-03-05 21:44:21','2026-03-05 21:44:21'),(2055,7,9014,1,1,1,1,'2026-03-05 21:44:21','2026-03-05 21:44:21'),(2056,7,9015,1,1,1,1,'2026-03-05 21:44:21','2026-03-05 21:44:21'),(2057,7,9016,1,0,0,0,'2026-03-05 21:44:21','2026-03-05 21:44:21'),(2058,7,9017,1,0,0,0,'2026-03-05 21:44:21','2026-03-05 21:44:21'),(2059,7,9018,1,0,0,0,'2026-03-05 21:44:21','2026-03-05 21:44:21'),(2060,7,9019,1,0,0,0,'2026-03-05 21:44:21','2026-03-05 21:44:21'),(2061,7,9020,1,0,0,0,'2026-03-05 21:44:21','2026-03-05 21:44:21'),(2062,7,9021,1,1,1,1,'2026-03-05 21:44:21','2026-03-05 21:44:21'),(2063,7,9022,1,0,0,0,'2026-03-05 21:44:21','2026-03-05 21:44:21'),(2064,7,9023,1,1,1,1,'2026-03-05 21:44:21','2026-03-05 21:44:21'),(2082,3,5005,1,0,0,0,'2022-07-13 09:54:15','2026-03-06 19:05:29'),(2083,2,5005,1,0,0,0,'2022-07-13 10:20:33','2026-03-06 19:05:29'),(2084,6,5005,1,0,0,0,'2022-07-13 10:29:49','2026-03-06 19:05:29'),(2085,1,5005,1,0,0,0,'2022-07-13 10:57:22','2026-03-06 19:05:29'),(2086,4,5005,1,0,0,0,'2022-07-13 11:23:43','2026-03-06 19:05:29'),(2087,3,5004,1,0,0,0,'2020-06-14 13:03:50','2026-03-06 19:05:29'),(2088,6,5004,1,0,0,0,'2022-07-13 10:29:49','2026-03-06 19:05:29'),(2089,2,5004,1,0,0,0,'2022-07-13 10:20:19','2026-03-06 19:05:29'),(2090,1,5004,1,0,0,0,'2022-07-13 10:57:22','2026-03-06 19:05:29'),(2091,4,5004,1,0,0,0,'2022-07-13 11:23:43','2026-03-06 19:05:29'),(2092,2,5003,1,1,0,0,'2022-07-13 10:21:13','2026-03-06 19:05:29'),(2093,6,5003,1,0,0,1,'2022-07-13 10:33:55','2026-03-06 19:05:29'),(2094,1,5003,1,1,0,1,'2022-07-13 11:00:28','2026-03-06 19:05:29'),(2095,4,5003,1,1,0,0,'2022-07-13 11:38:07','2026-03-06 19:05:29'),(2096,3,5003,1,0,0,0,'2020-06-14 13:03:50','2026-03-11 11:43:37'),(2097,2,5002,1,1,0,0,'2022-07-13 10:25:20','2026-03-06 19:05:29'),(2098,6,5002,1,1,0,1,'2022-07-13 10:52:36','2026-03-06 19:05:29'),(2099,1,5002,1,1,0,1,'2022-07-13 11:00:28','2026-03-06 19:05:29'),(2100,4,5002,1,1,0,0,'2022-07-13 11:38:07','2026-03-06 19:05:29'),(2101,2,5001,1,0,1,0,'2022-07-13 10:28:26','2026-03-06 19:05:29'),(2102,1,5001,1,0,1,0,'2022-07-13 11:00:36','2026-03-06 19:05:29'),(2103,6,5001,1,0,1,0,'2022-07-13 10:52:36','2026-03-06 19:05:29'),(2104,4,5001,1,0,0,0,'2022-07-13 11:49:49','2026-03-06 19:05:29'),(2105,1,15001,1,NULL,NULL,NULL,'2025-09-04 09:07:00','2025-10-04 06:41:16'),(2106,11,1,1,NULL,NULL,NULL,'2026-03-12 06:40:48','2026-03-12 06:40:48'),(2107,11,2,1,NULL,NULL,NULL,'2026-03-12 06:40:49','2026-03-12 06:40:49'),(2108,11,3,1,NULL,NULL,NULL,'2026-03-12 06:40:50','2026-03-12 06:40:50'),(2109,11,4,1,NULL,NULL,NULL,'2026-03-12 06:40:51','2026-03-12 06:40:51'),(2110,11,107,1,NULL,NULL,NULL,'2026-03-12 06:40:53','2026-03-12 06:40:53'),(2111,11,128,1,NULL,NULL,NULL,'2026-03-12 06:40:54','2026-03-12 06:40:54'),(2112,11,134,1,NULL,NULL,NULL,'2026-03-12 06:40:55','2026-03-12 06:40:55'),(2113,11,5,1,NULL,NULL,NULL,'2026-03-12 06:40:56','2026-03-12 06:40:56'),(2114,11,6,1,NULL,NULL,NULL,'2026-03-12 06:40:56','2026-03-12 06:40:56'),(2115,11,7,1,NULL,NULL,NULL,'2026-03-12 06:41:04','2026-03-12 06:41:04'),(2116,11,8,1,NULL,NULL,NULL,'2026-03-12 06:41:07','2026-03-12 06:41:07'),(2117,11,68,1,NULL,NULL,NULL,'2026-03-12 06:41:08','2026-03-12 06:41:08'),(2118,11,69,1,NULL,NULL,NULL,'2026-03-12 06:41:09','2026-03-12 06:41:09'),(2119,11,70,1,NULL,NULL,NULL,'2026-03-12 06:41:10','2026-03-12 06:41:10'),(2120,11,71,1,NULL,NULL,NULL,'2026-03-12 06:41:10','2026-03-12 06:41:10'),(2121,11,73,1,NULL,NULL,NULL,'2026-03-12 06:41:11','2026-03-12 06:41:11'),(2122,11,74,1,NULL,NULL,NULL,'2026-03-12 06:41:12','2026-03-12 06:41:12'),(2123,11,135,1,NULL,NULL,NULL,'2026-03-12 06:41:13','2026-03-12 06:41:13'),(2124,11,272,1,NULL,NULL,NULL,'2026-03-12 06:41:14','2026-03-12 06:41:14'),(2125,11,283,1,NULL,NULL,NULL,'2026-03-12 06:41:14','2026-03-12 06:41:14'),(2126,11,284,1,NULL,NULL,NULL,'2026-03-12 06:41:15','2026-03-12 06:41:15'),(2127,11,285,1,NULL,NULL,NULL,'2026-03-12 06:41:17','2026-03-12 06:41:17'),(2128,11,286,1,NULL,NULL,NULL,'2026-03-12 06:41:17','2026-03-12 06:41:17'),(2129,11,9,1,NULL,NULL,NULL,'2026-03-12 06:41:19','2026-03-12 06:41:19'),(2130,11,10,1,NULL,NULL,NULL,'2026-03-12 06:41:20','2026-03-12 06:41:20'),(2131,11,11,1,NULL,NULL,NULL,'2026-03-12 06:41:25','2026-03-12 06:41:25'),(2132,11,12,1,NULL,NULL,NULL,'2026-03-12 06:41:27','2026-03-12 06:41:27'),(2133,11,13,1,NULL,NULL,NULL,'2026-03-12 06:41:29','2026-03-12 06:41:29'),(2134,11,14,1,NULL,NULL,NULL,'2026-03-12 06:41:30','2026-03-12 06:41:30'),(2135,11,15,1,NULL,NULL,NULL,'2026-03-12 06:41:30','2026-03-12 06:41:30'),(2136,11,122,1,NULL,NULL,NULL,'2026-03-12 06:41:31','2026-03-12 06:41:31'),(2137,11,136,1,NULL,NULL,NULL,'2026-03-12 06:41:32','2026-03-12 06:41:32'),(2138,11,20,1,NULL,NULL,NULL,'2026-03-12 06:41:32','2026-03-12 06:41:32'),(2139,11,137,1,NULL,NULL,NULL,'2026-03-12 06:41:33','2026-03-12 06:41:33'),(2140,11,141,1,NULL,NULL,NULL,'2026-03-12 06:41:35','2026-03-12 06:41:35'),(2141,11,142,1,NULL,NULL,NULL,'2026-03-12 06:41:36','2026-03-12 06:41:36'),(2142,11,143,1,NULL,NULL,NULL,'2026-03-12 06:41:36','2026-03-12 06:41:36'),(2143,11,144,1,NULL,NULL,NULL,'2026-03-12 06:41:37','2026-03-12 06:41:37'),(2144,11,187,1,NULL,NULL,NULL,'2026-03-12 06:41:38','2026-03-12 06:41:38'),(2145,11,196,1,NULL,NULL,NULL,'2026-03-12 06:41:39','2026-03-12 06:41:39'),(2146,11,205,1,NULL,NULL,NULL,'2026-03-12 06:41:39','2026-03-12 06:41:39'),(2147,11,207,1,NULL,NULL,NULL,'2026-03-12 06:41:40','2026-03-12 06:41:40'),(2148,11,208,1,NULL,NULL,NULL,'2026-03-12 06:41:41','2026-03-12 06:41:41'),(2149,11,210,1,NULL,NULL,NULL,'2026-03-12 06:41:42','2026-03-12 06:41:42'),(2150,11,211,1,NULL,NULL,NULL,'2026-03-12 06:41:43','2026-03-12 06:41:43'),(2151,11,212,1,NULL,NULL,NULL,'2026-03-12 06:41:44','2026-03-12 06:41:44'),(2152,11,251,1,NULL,NULL,NULL,'2026-03-12 06:41:45','2026-03-12 06:41:45'),(2153,11,266,1,NULL,NULL,NULL,'2026-03-12 06:41:45','2026-03-12 06:41:45'),(2154,11,267,1,NULL,NULL,NULL,'2026-03-12 06:41:46','2026-03-12 06:41:46'),(2155,11,21,1,NULL,NULL,NULL,'2026-03-12 06:41:47','2026-03-12 06:41:47'),(2156,11,23,1,NULL,NULL,NULL,'2026-03-12 06:41:48','2026-03-12 06:41:48'),(2157,11,24,1,NULL,NULL,NULL,'2026-03-12 06:41:49','2026-03-12 06:41:49'),(2158,11,25,1,NULL,NULL,NULL,'2026-03-12 06:41:50','2026-03-12 06:41:50'),(2159,11,26,1,NULL,NULL,NULL,'2026-03-12 06:41:51','2026-03-12 06:41:51'),(2160,11,77,1,NULL,NULL,NULL,'2026-03-12 06:41:51','2026-03-12 06:41:51'),(2161,11,145,1,NULL,NULL,NULL,'2026-03-12 06:41:53','2026-03-12 06:41:53'),(2162,11,188,1,NULL,NULL,NULL,'2026-03-12 06:41:54','2026-03-12 06:41:54'),(2163,11,27,1,NULL,NULL,NULL,'2026-03-12 06:41:54','2026-03-12 06:41:54'),(2164,11,268,1,NULL,NULL,NULL,'2026-03-12 06:41:55','2026-03-12 06:41:55'),(2165,11,269,1,NULL,NULL,NULL,'2026-03-12 06:41:56','2026-03-12 06:41:56'),(2166,11,270,1,NULL,NULL,NULL,'2026-03-12 06:41:58','2026-03-12 06:41:58'),(2167,11,10008,1,NULL,NULL,NULL,'2026-03-12 06:41:59','2026-03-12 06:41:59'),(2168,11,10009,1,NULL,NULL,NULL,'2026-03-12 06:42:00','2026-03-12 06:42:00'),(2169,11,10010,1,NULL,NULL,NULL,'2026-03-12 06:42:01','2026-03-12 06:42:01'),(2170,11,10011,1,NULL,NULL,NULL,'2026-03-12 06:42:02','2026-03-12 06:42:02'),(2171,11,10012,1,NULL,NULL,NULL,'2026-03-12 06:42:03','2026-03-12 06:42:03'),(2172,11,10013,1,NULL,NULL,NULL,'2026-03-12 06:42:04','2026-03-12 06:42:04'),(2173,11,10014,1,NULL,NULL,NULL,'2026-03-12 06:42:05','2026-03-12 06:42:05'),(2174,11,10015,1,NULL,NULL,NULL,'2026-03-12 06:42:06','2026-03-12 06:42:06'),(2175,11,10016,1,NULL,NULL,NULL,'2026-03-12 06:42:07','2026-03-12 06:42:07'),(2176,11,10017,1,NULL,NULL,NULL,'2026-03-12 06:42:08','2026-03-12 06:42:08'),(2177,11,10018,1,NULL,NULL,NULL,'2026-03-12 06:42:08','2026-03-12 06:42:08'),(2178,11,10019,1,NULL,NULL,NULL,'2026-03-12 06:42:09','2026-03-12 06:42:09'),(2179,11,10020,1,NULL,NULL,NULL,'2026-03-12 06:42:19','2026-03-12 06:42:19'),(2180,11,10021,1,NULL,NULL,NULL,'2026-03-12 06:42:19','2026-03-12 06:42:19'),(2181,11,10022,1,NULL,NULL,NULL,'2026-03-12 06:42:21','2026-03-12 06:42:21'),(2182,11,31,1,NULL,NULL,NULL,'2026-03-12 06:42:21','2026-03-12 06:42:21'),(2183,11,32,1,NULL,NULL,NULL,'2026-03-12 06:42:22','2026-03-12 06:42:22'),(2184,11,33,1,NULL,NULL,NULL,'2026-03-12 06:42:24','2026-03-12 06:42:24'),(2185,11,34,1,NULL,NULL,NULL,'2026-03-12 06:42:25','2026-03-12 06:42:25'),(2186,11,35,1,NULL,NULL,NULL,'2026-03-12 06:42:25','2026-03-12 06:42:25'),(2187,11,104,1,NULL,NULL,NULL,'2026-03-12 06:42:26','2026-03-12 06:42:26'),(2188,11,37,1,NULL,NULL,NULL,'2026-03-12 06:42:27','2026-03-12 06:42:27'),(2189,11,38,1,NULL,NULL,NULL,'2026-03-12 06:42:28','2026-03-12 06:42:28'),(2190,11,39,1,NULL,NULL,NULL,'2026-03-12 06:42:28','2026-03-12 06:42:28'),(2191,11,259,1,NULL,NULL,NULL,'2026-03-12 06:42:29','2026-03-12 06:42:29'),(2192,11,260,1,NULL,NULL,NULL,'2026-03-12 06:42:30','2026-03-12 06:42:30'),(2193,11,261,1,NULL,NULL,NULL,'2026-03-12 06:42:31','2026-03-12 06:42:31'),(2194,11,262,1,NULL,NULL,NULL,'2026-03-12 06:42:31','2026-03-12 06:42:31'),(2195,11,40,1,NULL,NULL,NULL,'2026-03-12 06:42:32','2026-03-12 06:42:32'),(2196,11,41,1,NULL,NULL,NULL,'2026-03-12 06:42:33','2026-03-12 06:42:33'),(2197,11,42,1,NULL,NULL,NULL,'2026-03-12 06:42:34','2026-03-12 06:42:34'),(2198,11,43,1,NULL,NULL,NULL,'2026-03-12 06:42:36','2026-03-12 06:42:36'),(2199,11,44,1,NULL,NULL,NULL,'2026-03-12 06:42:37','2026-03-12 06:42:37'),(2200,11,46,1,NULL,NULL,NULL,'2026-03-12 06:42:38','2026-03-12 06:42:38'),(2201,11,217,1,NULL,NULL,NULL,'2026-03-12 06:42:40','2026-03-12 06:42:40'),(2202,11,252,1,NULL,NULL,NULL,'2026-03-12 06:42:41','2026-03-12 06:42:41'),(2203,11,253,1,NULL,NULL,NULL,'2026-03-12 06:42:41','2026-03-12 06:42:41'),(2204,11,254,1,NULL,NULL,NULL,'2026-03-12 06:42:42','2026-03-12 06:42:42'),(2205,11,255,1,NULL,NULL,NULL,'2026-03-12 06:42:43','2026-03-12 06:42:43'),(2206,11,146,1,NULL,NULL,NULL,'2026-03-12 06:42:44','2026-03-12 06:42:44'),(2207,11,147,1,NULL,NULL,NULL,'2026-03-12 06:42:45','2026-03-12 06:42:45'),(2208,11,148,1,NULL,NULL,NULL,'2026-03-12 06:42:45','2026-03-12 06:42:45'),(2209,11,149,1,NULL,NULL,NULL,'2026-03-12 06:42:46','2026-03-12 06:42:46'),(2210,11,150,1,NULL,NULL,NULL,'2026-03-12 06:42:47','2026-03-12 06:42:47'),(2211,11,151,1,NULL,NULL,NULL,'2026-03-12 06:42:48','2026-03-12 06:42:48'),(2212,11,152,1,NULL,NULL,NULL,'2026-03-12 06:42:49','2026-03-12 06:42:49'),(2213,11,153,1,NULL,NULL,NULL,'2026-03-12 06:42:50','2026-03-12 06:42:50'),(2214,11,154,1,NULL,NULL,NULL,'2026-03-12 06:42:52','2026-03-12 06:42:52'),(2215,11,155,1,NULL,NULL,NULL,'2026-03-12 06:42:53','2026-03-12 06:42:53'),(2216,11,156,1,NULL,NULL,NULL,'2026-03-12 06:42:54','2026-03-12 06:42:54'),(2217,11,157,1,NULL,NULL,NULL,'2026-03-12 06:42:55','2026-03-12 06:42:55'),(2218,11,158,1,NULL,NULL,NULL,'2026-03-12 06:42:56','2026-03-12 06:42:56'),(2219,11,159,1,NULL,NULL,NULL,'2026-03-12 06:42:57','2026-03-12 06:42:57'),(2220,11,160,1,NULL,NULL,NULL,'2026-03-12 06:42:58','2026-03-12 06:42:58'),(2221,11,161,1,NULL,NULL,NULL,'2026-03-12 06:42:59','2026-03-12 06:42:59'),(2222,11,162,1,NULL,NULL,NULL,'2026-03-12 06:43:00','2026-03-12 06:43:00'),(2223,11,164,1,NULL,NULL,NULL,'2026-03-12 06:43:01','2026-03-12 06:43:01'),(2224,11,163,1,NULL,NULL,NULL,'2026-03-12 06:43:02','2026-03-12 06:43:02'),(2225,11,165,1,NULL,NULL,NULL,'2026-03-12 06:43:03','2026-03-12 06:43:03'),(2226,11,174,1,NULL,NULL,NULL,'2026-03-12 06:43:04','2026-03-12 06:43:04'),(2227,11,175,1,NULL,NULL,NULL,'2026-03-12 06:43:05','2026-03-12 06:43:05'),(2228,11,176,1,NULL,NULL,NULL,'2026-03-12 06:43:06','2026-03-12 06:43:06'),(2229,11,177,1,NULL,NULL,NULL,'2026-03-12 06:43:06','2026-03-12 06:43:06'),(2230,11,178,1,NULL,NULL,NULL,'2026-03-12 06:43:07','2026-03-12 06:43:07'),(2231,11,179,1,NULL,NULL,NULL,'2026-03-12 06:43:08','2026-03-12 06:43:08'),(2232,11,180,1,NULL,NULL,NULL,'2026-03-12 06:43:09','2026-03-12 06:43:09'),(2233,11,181,1,NULL,NULL,NULL,'2026-03-12 06:43:12','2026-03-12 06:43:12'),(2234,11,182,1,NULL,NULL,NULL,'2026-03-12 06:43:13','2026-03-12 06:43:13'),(2235,11,183,1,NULL,NULL,NULL,'2026-03-12 06:43:14','2026-03-12 06:43:14'),(2236,11,197,1,NULL,NULL,NULL,'2026-03-12 06:43:15','2026-03-12 06:43:15'),(2237,11,198,1,NULL,NULL,NULL,'2026-03-12 06:43:16','2026-03-12 06:43:16'),(2238,11,200,1,NULL,NULL,NULL,'2026-03-12 06:43:17','2026-03-12 06:43:17'),(2239,11,201,1,NULL,NULL,NULL,'2026-03-12 06:43:17','2026-03-12 06:43:17'),(2240,11,202,1,NULL,NULL,NULL,'2026-03-12 06:43:18','2026-03-12 06:43:18'),(2241,11,203,1,NULL,NULL,NULL,'2026-03-12 06:43:19','2026-03-12 06:43:19'),(2242,11,204,1,NULL,NULL,NULL,'2026-03-12 06:43:19','2026-03-12 06:43:19'),(2243,11,219,1,NULL,NULL,NULL,'2026-03-12 06:43:20','2026-03-12 06:43:20'),(2244,11,220,1,NULL,NULL,NULL,'2026-03-12 06:43:21','2026-03-12 06:43:21'),(2245,11,221,1,NULL,NULL,NULL,'2026-03-12 06:43:22','2026-03-12 06:43:22'),(2246,11,223,1,NULL,NULL,NULL,'2026-03-12 06:43:23','2026-03-12 06:43:23'),(2247,11,240,1,NULL,NULL,NULL,'2026-03-12 06:43:23','2026-03-12 06:43:23'),(2248,11,241,1,NULL,NULL,NULL,'2026-03-12 06:43:24','2026-03-12 06:43:24'),(2249,11,242,1,NULL,NULL,NULL,'2026-03-12 06:43:25','2026-03-12 06:43:25'),(2250,11,244,1,NULL,NULL,NULL,'2026-03-12 06:43:26','2026-03-12 06:43:26'),(2251,11,245,1,NULL,NULL,NULL,'2026-03-12 06:43:27','2026-03-12 06:43:27'),(2252,11,246,1,NULL,NULL,NULL,'2026-03-12 06:43:30','2026-03-12 06:43:30'),(2253,11,256,1,NULL,NULL,NULL,'2026-03-12 06:43:30','2026-03-12 06:43:30'),(2254,11,257,1,NULL,NULL,NULL,'2026-03-12 06:43:31','2026-03-12 06:43:31'),(2255,11,258,1,NULL,NULL,NULL,'2026-03-12 06:43:32','2026-03-12 06:43:32'),(2256,11,276,1,NULL,NULL,NULL,'2026-03-12 06:43:33','2026-03-12 06:43:33'),(2257,11,54,1,NULL,NULL,NULL,'2026-03-12 06:43:34','2026-03-12 06:43:34'),(2258,11,55,1,NULL,NULL,NULL,'2026-03-12 06:43:35','2026-03-12 06:43:35'),(2259,11,56,1,NULL,NULL,NULL,'2026-03-12 06:43:35','2026-03-12 06:43:35'),(2260,11,57,1,NULL,NULL,NULL,'2026-03-12 06:43:36','2026-03-12 06:43:36'),(2261,11,58,1,NULL,NULL,NULL,'2026-03-12 06:43:37','2026-03-12 06:43:37'),(2262,11,59,1,NULL,NULL,NULL,'2026-03-12 06:43:38','2026-03-12 06:43:38'),(2263,11,60,1,NULL,NULL,NULL,'2026-03-12 06:43:38','2026-03-12 06:43:38'),(2264,11,126,1,NULL,NULL,NULL,'2026-03-12 06:43:39','2026-03-12 06:43:39'),(2265,11,130,1,NULL,NULL,NULL,'2026-03-12 06:43:40','2026-03-12 06:43:40'),(2266,11,131,1,NULL,NULL,NULL,'2026-03-12 06:43:41','2026-03-12 06:43:41'),(2267,11,213,1,NULL,NULL,NULL,'2026-03-12 06:43:41','2026-03-12 06:43:41'),(2268,11,215,1,NULL,NULL,NULL,'2026-03-12 06:43:42','2026-03-12 06:43:42'),(2269,11,216,1,NULL,NULL,NULL,'2026-03-12 06:43:43','2026-03-12 06:43:43'),(2270,11,233,1,NULL,NULL,NULL,'2026-03-12 06:43:45','2026-03-12 06:43:45'),(2271,11,243,1,NULL,NULL,NULL,'2026-03-12 06:43:45','2026-03-12 06:43:45'),(2272,11,264,1,NULL,NULL,NULL,'2026-03-12 06:43:46','2026-03-12 06:43:46'),(2273,11,265,1,NULL,NULL,NULL,'2026-03-12 06:43:47','2026-03-12 06:43:47'),(2274,11,271,1,NULL,NULL,NULL,'2026-03-12 06:43:48','2026-03-12 06:43:48'),(2275,11,10028,1,NULL,NULL,NULL,'2026-03-12 06:43:49','2026-03-12 06:43:49'),(2276,11,61,1,NULL,NULL,NULL,'2026-03-12 06:43:49','2026-03-12 06:43:49'),(2277,11,62,1,NULL,NULL,NULL,'2026-03-12 06:43:50','2026-03-12 06:43:50'),(2278,11,63,1,NULL,NULL,NULL,'2026-03-12 06:43:51','2026-03-12 06:43:51'),(2279,11,64,1,NULL,NULL,NULL,'2026-03-12 06:43:51','2026-03-12 06:43:51'),(2280,11,65,1,NULL,NULL,NULL,'2026-03-12 06:43:52','2026-03-12 06:43:52'),(2281,11,66,1,NULL,NULL,NULL,'2026-03-12 06:43:53','2026-03-12 06:43:53'),(2282,11,67,1,NULL,NULL,NULL,'2026-03-12 06:43:57','2026-03-12 06:43:57'),(2283,11,78,1,NULL,NULL,NULL,'2026-03-12 06:43:58','2026-03-12 06:43:58'),(2284,11,79,1,NULL,NULL,NULL,'2026-03-12 06:43:59','2026-03-12 06:43:59'),(2285,11,80,1,NULL,NULL,NULL,'2026-03-12 06:43:59','2026-03-12 06:43:59'),(2286,11,81,1,NULL,NULL,NULL,'2026-03-12 06:44:01','2026-03-12 06:44:01'),(2287,11,82,1,NULL,NULL,NULL,'2026-03-12 06:44:02','2026-03-12 06:44:02'),(2288,11,83,1,NULL,NULL,NULL,'2026-03-12 06:44:03','2026-03-12 06:44:03'),(2289,11,84,1,NULL,NULL,NULL,'2026-03-12 06:44:03','2026-03-12 06:44:03'),(2290,11,85,1,NULL,NULL,NULL,'2026-03-12 06:44:04','2026-03-12 06:44:04'),(2291,11,86,1,NULL,NULL,NULL,'2026-03-12 06:44:05','2026-03-12 06:44:05'),(2292,11,87,1,NULL,NULL,NULL,'2026-03-12 06:44:06','2026-03-12 06:44:06'),(2293,11,88,1,NULL,NULL,NULL,'2026-03-12 06:44:07','2026-03-12 06:44:07'),(2294,11,90,1,NULL,NULL,NULL,'2026-03-12 06:44:07','2026-03-12 06:44:07'),(2295,11,108,1,NULL,NULL,NULL,'2026-03-12 06:44:14','2026-03-12 06:44:14'),(2296,11,109,1,1,NULL,NULL,'2026-03-12 06:44:14','2026-03-16 06:45:03'),(2297,11,110,1,NULL,NULL,NULL,'2026-03-12 06:44:15','2026-03-12 06:44:15'),(2298,11,111,1,NULL,NULL,NULL,'2026-03-12 06:44:16','2026-03-12 06:44:16'),(2299,11,112,1,NULL,NULL,NULL,'2026-03-12 06:44:17','2026-03-12 06:44:17'),(2300,11,127,1,NULL,NULL,NULL,'2026-03-12 06:44:18','2026-03-12 06:44:18'),(2301,11,129,1,NULL,NULL,NULL,'2026-03-12 06:44:19','2026-03-12 06:44:19'),(2302,11,189,1,NULL,NULL,NULL,'2026-03-12 06:44:19','2026-03-12 06:44:19'),(2303,11,10025,1,NULL,NULL,NULL,'2026-03-12 06:44:22','2026-03-12 06:44:22'),(2304,11,10026,1,NULL,NULL,NULL,'2026-03-12 06:44:23','2026-03-12 06:44:23'),(2305,11,10027,1,NULL,NULL,NULL,'2026-03-12 06:44:24','2026-03-12 06:44:24'),(2306,11,93,1,NULL,NULL,NULL,'2026-03-12 06:44:25','2026-03-12 06:44:25'),(2307,11,94,1,NULL,NULL,NULL,'2026-03-12 06:44:26','2026-03-12 06:44:26'),(2308,11,250,1,NULL,NULL,NULL,'2026-03-12 06:44:27','2026-03-12 06:44:27'),(2309,11,96,1,NULL,NULL,NULL,'2026-03-12 06:44:27','2026-03-12 06:44:27'),(2310,11,97,1,NULL,NULL,NULL,'2026-03-12 06:44:28','2026-03-12 06:44:28'),(2311,11,98,1,NULL,NULL,NULL,'2026-03-12 06:44:29','2026-03-12 06:44:29'),(2312,11,99,1,NULL,NULL,NULL,'2026-03-12 06:44:30','2026-03-12 06:44:30'),(2313,11,248,1,NULL,NULL,NULL,'2026-03-12 06:44:30','2026-03-12 06:44:30'),(2314,11,249,1,NULL,NULL,NULL,'2026-03-12 06:44:31','2026-03-12 06:44:31'),(2315,11,102,1,NULL,NULL,NULL,'2026-03-12 06:44:32','2026-03-12 06:44:32'),(2316,11,106,1,NULL,NULL,NULL,'2026-03-12 06:44:33','2026-03-12 06:44:33'),(2317,11,113,1,NULL,NULL,NULL,'2026-03-12 06:44:33','2026-03-12 06:44:33'),(2318,11,114,1,NULL,NULL,NULL,'2026-03-12 06:44:34','2026-03-12 06:44:34'),(2319,11,115,1,NULL,NULL,NULL,'2026-03-12 06:44:36','2026-03-12 06:44:36'),(2320,11,116,1,NULL,NULL,NULL,'2026-03-12 06:44:37','2026-03-12 06:44:37'),(2321,11,117,1,NULL,NULL,NULL,'2026-03-12 06:44:38','2026-03-12 06:44:38'),(2322,11,118,1,NULL,NULL,NULL,'2026-03-12 06:44:39','2026-03-12 06:44:39'),(2323,11,190,1,NULL,NULL,NULL,'2026-03-12 06:44:40','2026-03-12 06:44:40'),(2324,11,191,1,NULL,NULL,NULL,'2026-03-12 06:44:40','2026-03-12 06:44:40'),(2325,11,192,1,NULL,NULL,NULL,'2026-03-12 06:44:41','2026-03-12 06:44:41'),(2326,11,193,1,NULL,NULL,NULL,'2026-03-12 06:44:42','2026-03-12 06:44:42'),(2327,11,194,1,NULL,NULL,NULL,'2026-03-12 06:44:43','2026-03-12 06:44:43'),(2328,11,195,1,NULL,NULL,NULL,'2026-03-12 06:44:43','2026-03-12 06:44:43'),(2329,11,226,1,NULL,NULL,NULL,'2026-03-12 06:44:44','2026-03-12 06:44:44'),(2330,11,227,1,NULL,NULL,NULL,'2026-03-12 06:44:45','2026-03-12 06:44:45'),(2331,11,229,1,NULL,NULL,NULL,'2026-03-12 06:44:46','2026-03-12 06:44:46'),(2332,11,230,1,NULL,NULL,NULL,'2026-03-12 06:44:46','2026-03-12 06:44:46'),(2333,11,280,1,NULL,NULL,NULL,'2026-03-12 06:44:49','2026-03-12 06:44:49'),(2334,11,281,1,NULL,NULL,NULL,'2026-03-12 06:44:50','2026-03-12 06:44:50'),(2335,11,282,1,NULL,NULL,NULL,'2026-03-12 06:44:51','2026-03-12 06:44:51'),(2336,11,10029,1,NULL,NULL,NULL,'2026-03-12 06:44:51','2026-03-12 06:44:51'),(2337,11,185,1,NULL,NULL,NULL,'2026-03-12 06:44:52','2026-03-12 06:44:52'),(2338,11,186,1,NULL,NULL,NULL,'2026-03-12 06:44:53','2026-03-12 06:44:53'),(2339,11,214,1,NULL,NULL,NULL,'2026-03-12 06:44:54','2026-03-12 06:44:54'),(2340,11,222,1,NULL,NULL,NULL,'2026-03-12 06:44:55','2026-03-12 06:44:55'),(2341,11,247,1,NULL,NULL,NULL,'2026-03-12 06:44:55','2026-03-12 06:44:55'),(2342,11,224,1,NULL,NULL,NULL,'2026-03-12 06:44:56','2026-03-12 06:44:56'),(2343,11,231,1,NULL,NULL,NULL,'2026-03-12 06:44:57','2026-03-12 06:44:57'),(2344,11,232,1,NULL,NULL,NULL,'2026-03-12 06:44:58','2026-03-12 06:44:58'),(2345,11,234,1,NULL,NULL,NULL,'2026-03-12 06:45:00','2026-03-12 06:45:00'),(2346,11,235,1,NULL,NULL,NULL,'2026-03-12 06:45:02','2026-03-12 06:45:02'),(2347,11,236,1,NULL,NULL,NULL,'2026-03-12 06:45:03','2026-03-12 06:45:03'),(2348,11,237,1,NULL,NULL,NULL,'2026-03-12 06:45:04','2026-03-12 06:45:04'),(2349,11,238,1,NULL,NULL,NULL,'2026-03-12 06:45:05','2026-03-12 06:45:05'),(2350,11,239,1,NULL,NULL,NULL,'2026-03-12 06:45:06','2026-03-12 06:45:06'),(2351,11,263,1,NULL,NULL,NULL,'2026-03-12 06:45:07','2026-03-12 06:45:07'),(2352,11,273,1,NULL,NULL,NULL,'2026-03-12 06:45:08','2026-03-12 06:45:08'),(2353,11,274,1,NULL,NULL,NULL,'2026-03-12 06:45:09','2026-03-12 06:45:09'),(2354,11,275,1,NULL,NULL,NULL,'2026-03-12 06:45:16','2026-03-12 06:45:16'),(2355,11,277,1,NULL,NULL,NULL,'2026-03-12 06:45:17','2026-03-12 06:45:17'),(2356,11,278,1,NULL,NULL,NULL,'2026-03-12 06:45:18','2026-03-12 06:45:18'),(2357,11,279,1,NULL,NULL,NULL,'2026-03-12 06:45:18','2026-03-12 06:45:18'),(2358,11,5001,1,NULL,NULL,NULL,'2026-03-12 06:45:19','2026-03-12 06:45:19'),(2359,11,5002,1,NULL,NULL,NULL,'2026-03-12 06:45:20','2026-03-12 06:45:20'),(2360,11,5003,1,NULL,NULL,NULL,'2026-03-12 06:45:20','2026-03-12 06:45:20'),(2361,11,5004,1,NULL,NULL,NULL,'2026-03-12 06:45:21','2026-03-12 06:45:21'),(2362,11,5005,1,NULL,NULL,NULL,'2026-03-12 06:45:22','2026-03-12 06:45:22'),(2363,11,9001,1,NULL,NULL,NULL,'2026-03-12 06:45:23','2026-03-12 06:45:23'),(2364,11,9002,1,NULL,NULL,NULL,'2026-03-12 06:45:23','2026-03-12 06:45:23'),(2365,11,9003,1,NULL,NULL,NULL,'2026-03-12 06:45:24','2026-03-12 06:45:24'),(2366,11,9004,1,NULL,NULL,NULL,'2026-03-12 06:45:25','2026-03-12 06:45:25'),(2367,11,9005,1,NULL,NULL,NULL,'2026-03-12 06:45:27','2026-03-12 06:45:27'),(2368,11,9006,1,NULL,NULL,NULL,'2026-03-12 06:45:28','2026-03-12 06:45:28'),(2369,11,9007,1,NULL,NULL,NULL,'2026-03-12 06:45:29','2026-03-12 06:45:29'),(2370,11,9008,1,NULL,NULL,NULL,'2026-03-12 06:45:29','2026-03-12 06:45:29'),(2371,11,9009,1,NULL,NULL,NULL,'2026-03-12 06:45:30','2026-03-12 06:45:30'),(2372,11,9010,1,NULL,NULL,NULL,'2026-03-12 06:45:31','2026-03-12 06:45:31'),(2373,11,9011,1,NULL,NULL,NULL,'2026-03-12 06:45:32','2026-03-12 06:45:32'),(2374,11,9012,1,NULL,NULL,NULL,'2026-03-12 06:45:32','2026-03-12 06:45:32'),(2375,11,9013,1,NULL,NULL,NULL,'2026-03-12 06:45:33','2026-03-12 06:45:33'),(2376,11,9014,1,NULL,NULL,NULL,'2026-03-12 06:45:34','2026-03-12 06:45:34'),(2377,11,9016,1,NULL,NULL,NULL,'2026-03-12 06:45:35','2026-03-12 06:45:35'),(2378,11,9015,1,NULL,NULL,NULL,'2026-03-12 06:45:36','2026-03-12 06:45:36'),(2379,11,9017,1,NULL,NULL,NULL,'2026-03-12 06:45:37','2026-03-12 06:45:37'),(2380,11,9018,1,NULL,NULL,NULL,'2026-03-12 06:45:38','2026-03-12 06:45:38'),(2381,11,9019,1,NULL,NULL,NULL,'2026-03-12 06:45:49','2026-03-12 06:45:49'),(2382,11,9020,1,NULL,NULL,NULL,'2026-03-12 06:45:51','2026-03-12 06:45:51'),(2383,11,9021,1,NULL,NULL,NULL,'2026-03-12 06:45:52','2026-03-12 06:45:52'),(2384,11,9022,1,NULL,NULL,NULL,'2026-03-12 06:45:52','2026-03-12 06:45:52'),(2385,11,9023,1,NULL,NULL,NULL,'2026-03-12 06:45:53','2026-03-12 06:45:53'),(2386,11,10001,1,NULL,NULL,NULL,'2026-03-12 06:45:55','2026-03-12 06:45:55'),(2387,11,10002,1,NULL,NULL,NULL,'2026-03-12 06:45:56','2026-03-12 06:45:56'),(2388,11,10003,1,NULL,NULL,NULL,'2026-03-12 06:45:57','2026-03-12 06:45:57'),(2389,11,10004,1,NULL,NULL,NULL,'2026-03-12 06:45:57','2026-03-12 06:45:57'),(2390,11,10005,1,NULL,NULL,NULL,'2026-03-12 06:46:16','2026-03-12 06:46:16'),(2391,11,10006,1,NULL,NULL,NULL,'2026-03-12 06:46:16','2026-03-12 06:46:16'),(2392,11,10007,1,NULL,NULL,NULL,'2026-03-12 06:46:17','2026-03-12 06:46:17'),(2393,11,15001,1,NULL,NULL,NULL,'2026-03-12 06:46:17','2026-03-12 06:46:17'),(2394,14,1,0,NULL,NULL,NULL,'2026-03-13 09:14:41','2026-03-13 09:15:09'),(2395,14,2,0,NULL,NULL,NULL,'2026-03-13 09:14:41','2026-03-13 09:15:09'),(2396,14,3,0,NULL,NULL,NULL,'2026-03-13 09:14:42','2026-03-13 09:15:10'),(2397,14,4,0,NULL,NULL,NULL,'2026-03-13 09:14:43','2026-03-13 09:15:11'),(2398,14,107,0,NULL,NULL,NULL,'2026-03-13 09:14:44','2026-03-13 09:15:12'),(2399,14,128,0,NULL,NULL,NULL,'2026-03-13 09:14:44','2026-03-13 09:15:12'),(2400,14,134,0,NULL,NULL,NULL,'2026-03-13 09:14:46','2026-03-13 09:15:13'),(2401,4,109,1,1,0,0,'2026-03-16 06:45:03','2026-03-16 06:45:03'),(2402,6,109,1,1,0,0,'2026-03-16 06:45:03','2026-03-16 06:45:03'),(2403,12,109,1,1,0,0,'2026-03-16 06:45:03','2026-03-16 06:45:03'),(2404,13,109,1,1,0,0,'2026-03-16 06:45:03','2026-03-16 06:45:03'),(2405,14,109,1,1,0,0,'2026-03-16 06:45:03','2026-03-16 06:45:03'),(2408,11,15009,1,NULL,NULL,NULL,'2026-03-16 06:47:53','2026-03-16 06:47:53'),(2409,10,15009,1,NULL,NULL,NULL,'2026-03-16 06:47:53','2026-03-16 06:47:53'),(2410,9,15009,1,NULL,NULL,NULL,'2026-03-16 06:47:53','2026-03-16 06:47:53'),(2411,3,15009,1,0,0,0,'2026-03-16 06:47:53','2026-03-16 06:47:53'),(2412,1,15009,1,1,1,1,'2026-03-16 06:47:53','2026-03-16 06:47:53'),(2413,11,15011,1,NULL,NULL,NULL,'2026-03-16 06:47:53','2026-03-16 06:47:53'),(2414,10,15011,1,NULL,NULL,NULL,'2026-03-16 06:47:53','2026-03-16 06:47:53'),(2415,9,15011,1,NULL,NULL,NULL,'2026-03-16 06:47:53','2026-03-16 06:47:53'),(2416,3,15011,1,0,0,0,'2026-03-16 06:47:53','2026-03-16 06:47:53'),(2417,1,15011,1,1,1,1,'2026-03-16 06:47:53','2026-03-16 06:47:53'),(2418,11,15008,1,NULL,NULL,NULL,'2026-03-16 06:47:53','2026-03-16 06:47:53'),(2419,10,15008,1,NULL,NULL,NULL,'2026-03-16 06:47:53','2026-03-16 06:47:53'),(2420,9,15008,1,NULL,NULL,NULL,'2026-03-16 06:47:53','2026-03-16 06:47:53'),(2421,3,15008,1,0,0,0,'2026-03-16 06:47:53','2026-03-16 06:47:53'),(2422,1,15008,1,1,1,1,'2026-03-16 06:47:53','2026-03-16 06:47:53'),(2423,11,15010,1,NULL,NULL,NULL,'2026-03-16 06:47:53','2026-03-16 06:47:53'),(2424,10,15010,1,NULL,NULL,NULL,'2026-03-16 06:47:53','2026-03-16 06:47:53'),(2425,9,15010,1,NULL,NULL,NULL,'2026-03-16 06:47:53','2026-03-16 06:47:53'),(2426,3,15010,1,0,0,0,'2026-03-16 06:47:53','2026-03-16 06:47:53'),(2427,1,15010,1,1,1,1,'2026-03-16 06:47:53','2026-03-16 06:47:53'),(2428,11,15007,1,NULL,NULL,NULL,'2026-03-16 06:47:53','2026-03-16 06:47:53'),(2429,10,15007,1,NULL,NULL,NULL,'2026-03-16 06:47:53','2026-03-16 06:47:53'),(2430,9,15007,1,NULL,NULL,NULL,'2026-03-16 06:47:53','2026-03-16 06:47:53'),(2431,3,15007,1,0,0,0,'2026-03-16 06:47:53','2026-03-16 06:47:53'),(2432,1,15007,1,1,1,1,'2026-03-16 06:47:53','2026-03-16 06:47:53'),(2433,11,15004,1,NULL,NULL,NULL,'2026-03-16 06:47:53','2026-03-16 06:47:53'),(2434,10,15004,1,NULL,NULL,NULL,'2026-03-16 06:47:53','2026-03-16 06:47:53'),(2435,9,15004,1,NULL,NULL,NULL,'2026-03-16 06:47:53','2026-03-16 06:47:53'),(2436,3,15004,1,0,0,0,'2026-03-16 06:47:53','2026-03-16 06:47:53'),(2437,1,15004,1,1,1,1,'2026-03-16 06:47:53','2026-03-16 06:47:53'),(2438,11,15002,1,NULL,NULL,NULL,'2026-03-16 06:47:53','2026-03-16 06:47:53'),(2439,10,15002,1,NULL,NULL,NULL,'2026-03-16 06:47:53','2026-03-16 06:47:53'),(2440,9,15002,1,NULL,NULL,NULL,'2026-03-16 06:47:53','2026-03-16 06:47:53'),(2441,3,15002,1,0,0,0,'2026-03-16 06:47:53','2026-03-16 06:47:53'),(2442,1,15002,1,1,1,1,'2026-03-16 06:47:53','2026-03-16 06:47:53'),(2443,11,15003,1,NULL,NULL,NULL,'2026-03-16 06:47:53','2026-03-16 06:47:53'),(2444,10,15003,1,NULL,NULL,NULL,'2026-03-16 06:47:53','2026-03-16 06:47:53'),(2445,9,15003,1,NULL,NULL,NULL,'2026-03-16 06:47:53','2026-03-16 06:47:53'),(2446,3,15003,1,0,0,0,'2026-03-16 06:47:53','2026-03-16 06:47:53'),(2447,1,15003,1,1,1,1,'2026-03-16 06:47:53','2026-03-16 06:47:53'),(2448,11,15006,1,NULL,NULL,NULL,'2026-03-16 06:47:53','2026-03-16 06:47:53'),(2449,10,15006,1,NULL,NULL,NULL,'2026-03-16 06:47:53','2026-03-16 06:47:53'),(2450,9,15006,1,NULL,NULL,NULL,'2026-03-16 06:47:53','2026-03-16 06:47:53'),(2451,3,15006,1,0,0,0,'2026-03-16 06:47:53','2026-03-16 06:47:53'),(2452,1,15006,1,1,1,1,'2026-03-16 06:47:53','2026-03-16 06:47:53'),(2453,11,15005,1,NULL,NULL,NULL,'2026-03-16 06:47:53','2026-03-16 06:47:53'),(2454,10,15005,1,NULL,NULL,NULL,'2026-03-16 06:47:53','2026-03-16 06:47:53'),(2455,9,15005,1,NULL,NULL,NULL,'2026-03-16 06:47:53','2026-03-16 06:47:53'),(2456,3,15005,1,0,0,0,'2026-03-16 06:47:53','2026-03-16 06:47:53'),(2457,1,15005,1,1,1,1,'2026-03-16 06:47:53','2026-03-16 06:47:53'),(2471,9,79,1,NULL,NULL,NULL,'2026-03-25 08:50:35','2026-03-25 08:50:35'),(2472,9,78,1,NULL,NULL,NULL,'2026-03-25 08:50:51','2026-03-25 08:50:51'),(2473,7,15012,1,NULL,1,NULL,'2026-04-05 10:22:12','2026-04-05 10:22:12'),(2474,1,15012,1,NULL,1,NULL,'2026-04-05 10:22:12','2026-04-05 10:22:12'),(2476,8,85,1,1,1,1,'2026-04-06 04:49:16','2026-04-06 04:49:19');
/*!40000 ALTER TABLE `roles_permissions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `sidebar_menus`
--

LOCK TABLES `sidebar_menus` WRITE;
/*!40000 ALTER TABLE `sidebar_menus` DISABLE KEYS */;
INSERT INTO `sidebar_menus` VALUES (1,'',17,'fa fa-ioxhost ftlayer','Front Office','front_office','front_office',10,1,1,'(\'admission_enquiry\', \'can_view\') || (\'visitor_book\', \'can_view\') ||       (\'phon_call_log\', \'can_view\') ||  (\'postal_dispatch\', \'can_view\') ||       (\'postal_receive\', \'can_view\') || (\'complaint\', \'can_view\') ||(\'setup_font_office\', \'can_view\')',1,'2023-01-10 01:49:51','2025-09-25 04:40:51'),(2,'',1,'fa fa-user-plus ftlayer','Student Information','student_information','student_information',20,2,1,'(\'student\', \'can_view\') || (\'student\', \'can_add\') || (\'student_history\', \'can_view\') || (\'student_categories\', \'can_view\') || (\'student_houses\', \'can_view\') || (\'disable_student\', \'can_view\') || (\'disable_reason\', \'can_view\') || (\'online_admission\', \'can_view\') || (\'multiclass_student\', \'can_view\') || (\'disable_reason\', \'can_view\') || (\'birthday_list\', \'can_view\')',1,'2023-01-10 01:49:51','2025-10-05 07:14:13'),(3,'',2,'fa fa-money ftlayer','Fees Collection','fees_collection','fees_collection',30,3,1,'(\'collect_fees\', \'can_view\') || (\'search_fees_payment\', \'can_view\') || (\'search_due_fees\', \'can_view\') || (\'fees_statement\', \'can_view\') || (\'fees_carry_forward\', \'can_view\') || (\'fees_master\', \'can_view\') || (\'fees_group\', \'can_view\') || (\'fees_type\', \'can_view\') || (\'fees_discount\', \'can_view\') || (\'accountants\', \'can_view\')',1,'2023-01-10 01:49:51','2025-09-25 04:40:51'),(4,'',3,'fa fa-usd ftlayer','Income','income','income',40,4,1,'(\'income\', \'can_view\') || (\'search_income\', \'can_view\') || (\'income_head\', \'can_view\')',1,'2023-01-10 01:49:37','2025-10-25 17:07:28'),(7,'',4,'fa fa-credit-card ftlayer','Expense','expense','expenses',50,5,1,'(\'expense\', \'can_view\') || (\'search_expense\', \'can_view\') || (\'expense_head\', \'can_view\')',1,'2023-01-10 01:49:37','2025-10-25 17:07:28'),(10,'',5,'fa fa-calendar-check-o ftlayer','Attendance','attendance','attendance',60,7,1,'(\'student_attendance\', \'can_view\')',1,'2023-01-10 01:49:37','2025-10-25 17:07:28'),(11,'',6,'fa fa-map-o ftlayer','Examinations','examinations','examinations',70,6,1,'(\'exam_group\', \'can_view\') || (\'exam_result\', \'can_view\') || (\'design_admit_card\', \'can_view\') || (\'print_admit_card\', \'can_view\') || (\'design_marksheet\', \'can_view\') || (\'print_marksheet\', \'can_view\') || (\'marks_grade\', \'can_view\')',1,'2023-01-10 01:49:37','2025-10-25 17:07:28'),(12,'',23,'fa fa-rss ftlayer','Online Examinations','online_examinations','online_examinations',80,8,1,'(\'online_examination\', \'can_view\') ||  (\'question_bank\', \'can_view\'',1,'2023-01-10 01:49:37','2025-10-25 17:07:28'),(13,'',29,'fa fa-list-alt ftlayer','Lesson Plan','lesson_plan','lesson_plan',90,10,1,'(\'manage_lesson_plan\', \'can_view\') || (\'manage_syllabus_status\', \'can_view\') || (\'lesson\', \'can_view\') ||  (\'topic\', \'can_view\')||  (\'copy_old_lesson\', \'can_view\')',1,'2023-01-10 01:49:37','2025-10-25 17:07:28'),(14,'',7,'fa fa-mortar-board ftlayer','Academics','academics','academics',100,9,1,'(\'class_timetable\', \'can_view\') || (\'teachers_timetable\', \'can_view\') || (\'assign_class_teacher\', \'can_view\') || (\'promote_student\', \'can_view\') || (\'subject_group\', \'can_view\') || (\'section\', \'can_view\') || (\'subject\', \'can_view\') || (\'class\', \'can_view\') || (\'section\', \'can_view\')',1,'2023-01-10 01:49:37','2025-10-25 17:07:28'),(15,'',18,'fa fa-sitemap ftlayer','Human Resource','human_resource','human_resource',110,12,1,'(\'staff\', \'can_view\') || (\'approve_leave_request\', \'can_view\') || (\'apply_leave\', \'can_view\') || (\'leave_types\', \'can_view\') || (\'teachers_rating\', \'can_view\') || (\'department\', \'can_view\') || (\'designation\', \'can_view\') || (\'disable_staff\', \'can_view\')',1,'2023-01-10 01:49:37','2025-10-25 17:07:28'),(16,'',13,'fa fa-bullhorn ftlayer','Communicate','communicate','communicate',120,13,1,'(\'notice_board\', \'can_view\') || (\'email\', \'can_view\') || (\'sms\', \'can_view\') || (\'email_sms_log\', \'can_view\')',1,'2023-01-10 01:49:37','2025-10-25 17:07:28'),(17,'',8,'fa fa-download ftlayer','Download Center','download_center','download_center',130,14,1,'(\'upload_content\', \'can_view\') || (\'video_tutorial\', \'can_view\') || (\'content_type\', \'can_view\') || (\'content_share_list\', \'can_view\')',1,'2023-01-10 01:49:37','2025-10-25 17:07:28'),(18,'',19,'fa fa-flask ftlayer','Homework','homework','homework',140,15,1,'(\'homework\', \'can_view\') || (\'homework\', \'can_view\')',1,'2023-01-10 01:49:37','2025-10-25 17:07:28'),(19,'',9,'fa fa-book ftlayer','Library','library','library',150,16,1,'(\'books\', \'can_view\') || (\'issue_return\', \'can_view\') || (\'add_staff_member\', \'can_view\') || (\'add_student\', \'can_view\') || (\'opaq\', \'can_view\')|| (\'library_category\', \'can_view\') || (\'library_subcategory\', \'can_view\') || (\'library_publisher\', \'can_view\') || (\'library_vendor\', \'can_view\') || (\'library_book_type\', \'can_view\') || (\'library_subject\', \'can_view\') || (\'library_position_rack\', \'can_view\') || (\'library_position_shelf\', \'can_view\') || (\'library_checkin_checkout\', \'can_view\') || (\'library_checkout_pending\', \'can_view\')\n',1,'2023-01-10 01:49:37','2025-10-25 17:07:28'),(20,'',10,'fa fa-object-group ftlayer','Inventory','inventory','inventory',160,19,1,'(\'issue_item\', \'can_view\') || (\'item_stock\', \'can_view\') || (\'item\', \'can_view\') || (\'item_category\', \'can_view\') || (\'item_category\', \'can_view\') || (\'store\', \'can_view\') || (\'supplier\', \'can_view\')',1,'2023-01-10 01:49:37','2025-10-25 17:07:28'),(21,'',11,'fa fa-bus ftlayer','Transport','transport','transport',170,20,1,'(\'routes\', \'can_view\') || (\'vehicle\', \'can_view\') || (\'assign_vehicle\', \'can_view\') || (\'transport_fees_master\', \'can_view\') || (\'pickup_point\', \'can_view\') || (\'route_pickup_point\', \'can_view\') || (\'student_transport_fees\', \'can_view\')      ',1,'2023-01-10 01:49:37','2025-10-25 17:07:28'),(22,'',12,'fa fa-building-o ftlayer','Hostel','hostel','hostel',180,22,1,'(\'hostel_rooms\', \'can_view\') || (\'room_type\', \'can_view\') || (\'hostel\', \'can_view\')',1,'2023-01-10 01:49:37','2025-10-25 17:07:28'),(23,'',20,'fa fa-newspaper-o ftlayer','Certificate','certificate','certificate',190,24,1,'(\'student_certificate\', \'can_view\') || (\'generate_certificate\', \'can_view\') || (\'student_id_card\', \'can_view\') || (\'generate_id_card\', \'can_view\') || (\'staff_id_card\', \'can_view\') || (\'generate_staff_id_card\', \'can_view\')',1,'2023-01-10 01:49:37','2025-10-25 17:07:44'),(24,'',16,'fa fa-empire ftlayer','Front CMS','front_cms','front_cms',200,26,1,'(\'event\', \'can_view\') || (\'gallery\', \'can_view\') || (\'notice\', \'can_view\') || (\'media_manager\', \'can_view\') || (\'pages\', \'can_view\') || (\'menus\', \'can_view\') || (\'banner_images\', \'can_view\')',1,'2023-01-10 01:49:37','2025-10-25 17:07:44'),(25,'',28,'fa fa-universal-access ftlayer','Alumni','alumni','alumni',210,23,1,'(\'manage_alumni\', \'can_view\') || (\'events\', \'can_view\')',1,'2023-01-10 01:49:37','2025-10-25 17:07:44'),(26,'',14,'fa fa-line-chart ftlayer','Reports','reports','reports',220,25,1,'(\'student_report\', \'can_view\') || (\'guardian_report\', \'can_view\') || (\'student_history\', \'can_view\') || (\'student_login_credential_report\', \'can_view\') || (\'class_subject_report\', \'can_view\') || (\'admission_report\', \'can_view\') || (\'sibling_report\', \'can_view\') || (\'evaluation_report\', \'can_view\') || (\'student_profile\', \'can_view\') || (\'fees_statement\', \'can_view\') || (\'balance_fees_report\', \'can_view\') || (\'fees_collection_report\', \'can_view\') || (\'online_fees_collection_report\', \'can_view\') || (\'income_report\', \'can_view\') || (\'expense_report\', \'can_view\') || (\'payroll_report\', \'can_view\') || (\'income_group_report\', \'can_view\') || (\'expense_group_report\', \'can_view\') || (\'attendance_report\', \'can_view\') || (\'staff_attendance_report\', \'can_view\') || (\'exam_marks_report\', \'can_view\') ||        (\'online_exam_wise_report\', \'can_view\') || (\'online_exams_report\', \'can_view\') || (\'online_exams_attempt_report\', \'can_view\') || (\'online_exams_rank_report\', \'can_view\') || (\'payroll_report\', \'can_view\') || (\'transport_report\', \'can_view\') || (\'hostel_report\', \'can_view\') || (\'audit_trail_report\', \'can_view\') || (\'user_log\', \'can_view\') || (\'book_issue_report\', \'can_view\') || (\'book_due_report\', \'can_view\') || (\'book_inventory_report\', \'can_view\') || (\'stock_report\', \'can_view\') ||      (\'add_item_report\', \'can_view\') || (\'issue_inventory_report\', \'can_view\') || (\'syllabus_status_report\', \'can_view\') ||    (\'teacher_syllabus_status_report\', \'can_view\') || (\'daily_collection_report\', \'can_view\') || (\'balance_fees_statement\', \'can_view\') || (\'balance_fees_report_with_remark\', \'can_view\')',1,'2023-01-10 01:49:37','2025-10-25 17:07:44'),(27,'',15,'fa fa-gears ftlayer','System Settings','system_settings','system_setting',230,27,1,'(\'general_setting\', \'can_view\') || (\'session_setting\', \'can_view\') || (\'notification_setting\', \'can_view\') || (\'sms_setting\', \'can_view\') || (\'email_setting\', \'can_view\') || (\'payment_methods\', \'can_view\') || (\'languages\', \'can_view\') || (\'user_status\', \'can_view\') || (\'backup_restore\', \'can_view\') || (\'print_header_footer\', \'can_view\') || (\'backup\', \'can_view\') || (\'front_cms_setting\', \'can_view\') || (\'custom_fields\', \'can_view\') || (\'system_fields\', \'can_view\') || (\'student_profile_update\', \'can_view\') || (\'currency\', \'can_view\') || (\'language_switcher\', \'can_view\') || (\'sidebar_menu\', \'can_view\') || (\'online_admission\', \'can_view\')\r\n',1,'2023-01-10 01:49:37','2025-10-25 17:07:28'),(30,'sszlc',500,'fa fa-video-camera ftlayer','Zoom Live Classes','zoom_live_classes','zoom_live_classes',0,6,1,'(\'setting\', \'can_view\') || (\'live_classes\', \'can_view\') || (\'live_meeting\', \'can_view\') || (\'live_classes_report\', \'can_view\') || (\'live_meeting_report\', \'can_view\')',1,'2023-01-10 12:49:51','2026-03-06 19:17:50'),(33,'ssmb',1000,'fa fa-sitemap ftlayer','Multi Branch','multi_branch','multi_branch',0,4,1,'(\'multi_branch_overview\', \'can_view\') || (\'multi_branch_daily_collection_report\', \'can_view\') || (\'multi_branch_payroll\', \'can_view\') || (\'multi_branch_income_report\', \'can_view\') || (\'multi_branch_expense_report\', \'can_view\') || (\'multi_branch_user_log_report\', \'can_view\') || (\'multi_branch_setting\', \'can_view\')',1,'2023-01-10 07:19:51','2025-11-08 01:43:33'),(34,'sscbse',900,'fa fa-file-text-o','CBSE Examination','cbse_exam','cbse_exam',69,11,1,'(\'subject_marks_report\', \'can_view\') || (\'template_marks_report\', \'can_view\') || (\'cbse_exam\', \'can_view\') || (\'cbse_exam_print_marksheet\', \'can_view\') || (\'cbse_exam_grade\', \'can_view\') || (\'cbse_exam_assign_observation\', \'can_view\') || (\'cbse_exam_observation\', \'can_view\') || (\'cbse_exam_observation_parameter\', \'can_view\') || (\'cbse_exam_assessment\', \'can_view\') || (\'cbse_exam_term\', \'can_view\') || (\'cbse_exam_template\', \'can_view\') || (\'cbse_exam_schedule\', \'can_view\') || (\'cbse_exam_category\', \'can_view\') || (\'cbse_exam_admit_card\', \'can_view\') || (\'cbse_exam_print_admit_card\', \'can_view\') || (\'cbse_exam_setting\', \'can_view\')',1,'2023-07-04 13:03:29','2026-03-05 21:44:21'),(36,'',30,'fa fa-calendar','Annual Calendar','holiday','annual_calendar',240,11,1,'(\'annual_calendar\', \'can_view\')\r\n',1,'2025-01-17 22:15:03','2025-10-25 17:07:28'),(37,'',31,'fa fa-ioxhost ftlayer','Student CV','student_cv','student_cv',1,21,1,'(\'download_cv\', \'can_view\') || (\'build_cv\', \'can_view\') || (\'resume_setting\', \'can_view\') || (\'student_resume_details\', \'can_view\')',1,'2025-01-17 22:15:07','2025-10-25 17:07:28'),(38,'',18,'fa fa-book ftlayer','Hall Management','hall_management','hall_management',150,17,1,'(\'hall_master\', \'can_view\') || (\'hall_bookings\', \'can_view\') || (\'approval_configuration\', \'can_view\')',1,'2023-01-10 01:49:37','2025-10-25 17:07:28'),(39,'',18,'fa fa-book ftlayer','NAAC','naac','naac',150,18,1,'(\'naac_configuration\', \'can_view\') || (\'naac_iiqa\', \'can_view\') || (\'naac_ssr\', \'can_view\') || (\'naac_aqar\', \'can_view\')',1,'2023-01-10 01:49:37','2025-10-25 17:07:28'),(40,'',17,'fa fa-graduation-cap','Admissions','admissions','admissions',0,0,1,'(\'admission_enquiry\', \'can_view\')||(\'online_admission\', \'can_view\')',1,'2026-02-11 20:29:16','2026-02-11 20:29:16');
/*!40000 ALTER TABLE `sidebar_menus` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `sidebar_sub_menus`
--

LOCK TABLES `sidebar_sub_menus` WRITE;
/*!40000 ALTER TABLE `sidebar_sub_menus` DISABLE KEYS */;
INSERT INTO `sidebar_sub_menus` VALUES (1,40,'admission_enquiry',NULL,'admission_enquiry','admin/enquiry',1,'(\'admission_enquiry\', \'can_view\')',NULL,'enquiry','index',NULL,1,'2022-07-11 01:04:46','2026-02-11 20:29:16'),(2,1,'visitor_book',NULL,'visitor_book','admin/visitors',2,'(\'visitor_book\', \'can_view\')',NULL,'visitors','index',NULL,1,'2022-07-11 01:04:46','2025-09-25 04:40:51'),(3,1,'phone_call_log',NULL,'phone_call_log','admin/generalcall',3,'(\'phone_call_log\', \'can_view\')',NULL,'generalcall','index,edit',NULL,1,'2022-05-10 00:40:07','2025-09-25 04:40:51'),(4,1,'postal_dispatch',NULL,'postal_dispatch','admin/dispatch',4,'(\'postal_dispatch\', \'can_view\')',NULL,'dispatch','index,editdispatch',NULL,1,'2022-05-10 00:40:09','2025-09-25 04:40:51'),(5,1,'postal_receive',NULL,'postal_receive','admin/receive',5,'(\'postal_receive\', \'can_view\')',NULL,'receive','index,editreceive',NULL,1,'2022-05-10 00:40:09','2025-09-25 04:40:51'),(6,1,'complain',NULL,'complain','admin/complaint',6,'(\'complaint\', \'can_view\')',NULL,'complaint','index,edit',NULL,1,'2022-05-10 00:40:00','2025-09-25 04:40:51'),(7,1,'setup_front_office',NULL,'setup_front_office','admin/visitorspurpose',7,'(\'setup_font_office\', \'can_view\')',NULL,'visitorspurpose','index,edit',NULL,1,'2022-04-18 04:43:15','2025-09-25 04:40:51'),(9,2,'student_admission',NULL,'student_admission','student/create',2,'(\'student\', \'can_add\')',NULL,'student','create,import',NULL,1,'2022-08-29 20:51:02','2025-09-25 04:40:51'),(10,40,'online_admission',NULL,'online_admission','admin/onlinestudent',3,'(\'online_admission\', \'can_view\')',27,'onlinestudent','index,edit',NULL,1,'2022-05-10 00:40:30','2026-02-11 20:29:16'),(11,2,'disable_student',NULL,'disabled_students','student/disablestudentslist',4,'(\'disable_student\', \'can_view\')',NULL,'student','disablestudentslist','',1,'2022-07-22 19:49:00','2025-09-25 04:40:51'),(12,3,'collect_fees',NULL,'collect_fees','studentfee',1,'(\'collect_fees\', \'can_view\')',NULL,'studentfee','index,addfee',NULL,1,'2022-07-22 19:53:34','2025-09-25 04:40:51'),(13,3,'search_fees_payment',NULL,'search_fees_payment','studentfee/searchpayment',3,'(\'search_fees_payment\', \'can_view\')',NULL,'studentfee','searchpayment',NULL,1,'2022-08-07 19:03:40','2025-09-25 04:40:51'),(14,3,'search_due_fees',NULL,'search_due_fees','studentfee/feesearch',4,'(\'search_due_fees\', \'can_view\')',NULL,'studentfee','feesearch',NULL,1,'2022-08-07 19:03:38','2025-09-25 04:40:51'),(15,3,'fees_master',NULL,'fees_master','admin/feemaster',5,'(\'fees_master\', \'can_view\')',NULL,'feemaster','index,assign,edit',NULL,1,'2022-09-23 20:35:55','2025-09-25 04:40:51'),(16,3,'fees_group',NULL,'fees_group','admin/feegroup',6,'(\'fees_group\', \'can_view\')',NULL,'feegroup','index,edit',NULL,1,'2022-08-07 19:03:32','2025-09-25 04:40:51'),(17,4,'add_income',NULL,'add_income','admin/income',1,'(\'income\', \'can_view\')',NULL,'income','index,edit',NULL,1,'2022-07-22 20:03:40','2025-09-25 04:40:51'),(18,4,'search_income',NULL,'search_income','admin/income/incomesearch',2,'(\'search_income\', \'can_view\')',NULL,'income','incomesearch',NULL,1,'2022-07-22 20:10:13','2025-09-25 04:40:51'),(19,4,'income_head',NULL,'income_head','admin/incomehead',3,'(\'income_head\', \'can_view\')',NULL,'incomehead','index,edit',NULL,1,'2022-07-22 20:11:11','2025-09-25 04:40:51'),(20,2,'student_details',NULL,'student_details','student/search',1,'(\'student\', \'can_view\')',NULL,'student','search,view,edit',NULL,1,'2022-08-29 20:51:05','2025-09-25 04:40:51'),(21,2,'multi_class_student',NULL,'multi_class_student','student/multiclass',5,'(\'multi_class_student\', \'can_view\')',26,'student','multiclass',NULL,1,'2022-07-22 19:48:37','2025-09-25 04:40:51'),(22,2,'bulk_delete',NULL,'bulk_delete','student/bulkdelete',6,'(\'student\', \'can_delete\')',NULL,'student','bulkdelete',NULL,1,'2022-07-22 19:48:11','2025-09-25 04:40:51'),(23,2,'student_categories',NULL,'student_categories','category',7,'(\'student_categories\', \'can_view\')',NULL,'category','index,edit',NULL,1,'2022-07-22 19:47:24','2025-09-25 04:40:51'),(24,2,'student_house',NULL,'student_house','admin/schoolhouse',8,'(\'student_houses\', \'can_view\')',NULL,'schoolhouse','index,edit',NULL,1,'2022-07-22 19:49:59','2025-09-25 04:40:51'),(25,2,'disable_reason',NULL,'disable_reason','admin/disable_reason',9,'(\'disable_reason\', \'can_view\')',NULL,'disable_reason','index,edit',NULL,1,'2022-07-22 19:50:41','2025-09-25 04:40:51'),(29,7,'add_expense',NULL,'add_expense','admin/expense',1,'(\'expense\', \'can_view\')',NULL,'expense','index,edit','',1,'2022-07-22 20:12:25','2025-09-25 04:40:51'),(32,3,'fees_type',NULL,'fees_type','admin/feetype',7,'(\'fees_type\', \'can_view\')',NULL,'feetype','index,edit','',1,'2022-08-07 19:03:29','2025-09-25 04:40:51'),(33,10,'attendance_by_date','attendance_by_date','attendance_by_date','admin/stuattendence/attendencereport',3,'(\'attendance_by_date\', \'can_view\')',NULL,'stuattendence','attendencereport','',0,'2022-10-19 18:50:25','2025-10-26 17:37:07'),(34,10,'student_attendance','student_attendance','student_attendance','admin/stuattendence',1,'(\'student_attendance\', \'can_view\')',NULL,'stuattendence','index','',0,'2022-10-19 18:50:25','2025-10-26 17:37:07'),(35,10,'approve_leave','approve_leave','approve_leave','admin/approve_leave',2,'(\'approve_leave\', \'can_view\')',NULL,'approve_leave','index','',1,'2022-10-14 05:16:44','2025-09-25 04:40:51'),(36,11,'exam_group',NULL,'exam_group','admin/examgroup',1,'(\'exam_group\', \'can_view\')',NULL,'examgroup','index,addexam,edit','',1,'2022-07-22 20:23:01','2025-09-25 04:40:51'),(37,11,'exam_schedule',NULL,'exam_schedule','admin/exam_schedule',2,'(\'student_attendance\', \'can_view\')',NULL,'exam_schedule','index','',1,'2022-05-15 20:01:34','2025-09-25 04:40:51'),(38,11,'exam_result',NULL,'exam_result','admin/examresult',3,'(\'exam_result\', \'can_view\')',NULL,'examresult','index','',1,'2022-05-15 20:01:34','2025-09-25 04:40:51'),(39,11,'design_admit_card',NULL,'design_admit_card','admin/admitcard',4,'(\'design_admit_card\', \'can_view\')',NULL,'admitcard','index,edit','',1,'2022-07-22 20:28:02','2025-09-25 04:40:51'),(40,11,'print_admit_card',NULL,'print_admit_card','admin/examresult/admitcard',5,'(\'print_admit_card\', \'can_view\')',NULL,'examresult','admitcard','',1,'2022-05-15 20:01:34','2025-09-25 04:40:51'),(41,11,'design_marksheet',NULL,'design_marksheet','admin/marksheet',6,'(\'design_marksheet\', \'can_view\')',NULL,'marksheet','index,edit','',1,'2022-07-22 20:35:35','2025-09-25 04:40:51'),(42,11,'print_marksheet',NULL,'print_marksheet','admin/examresult/marksheet',7,'(\'print_marksheet\', \'can_view\')',NULL,'examresult','marksheet','',1,'2022-05-15 20:01:38','2025-09-25 04:40:51'),(43,11,'marks_grade',NULL,'marks_grade','admin/grade',8,'(\'marks_grade\', \'can_view\')',NULL,'grade','index,edit','',1,'2022-07-22 20:37:15','2025-09-25 04:40:51'),(44,11,'marks_division',NULL,'marks_division','admin/marksdivision',9,'(\'marks_division\', \'can_view\')',NULL,'marksdivision','index,edit','',1,'2022-08-24 19:04:26','2025-09-25 04:40:51'),(45,12,'online_exam',NULL,'online_exam','admin/onlineexam',1,'(\'online_examination\', \'can_view\')',NULL,'onlineexam','index,evalution,assign','',1,'2022-08-30 02:03:45','2025-09-25 04:40:51'),(46,12,'question_bank',NULL,'question_bank','admin/question',1,'(\'question_bank\', \'can_view\')',NULL,'question','index,read','',1,'2022-08-30 00:03:13','2025-09-25 04:40:51'),(47,13,'manage_lesson_plan',NULL,'manage_lesson_plan','admin/syllabus',2,'(\'manage_lesson_plan\', \'can_view\')',NULL,'syllabus','index','',1,'2022-09-03 05:59:31','2025-09-25 04:40:51'),(48,13,'manage_syllabus_status',NULL,'manage_syllabus_status','admin/syllabus/status',3,'(\'manage_syllabus_status\', \'can_view\')',NULL,'syllabus','status','',1,'2022-09-03 05:59:35','2025-09-25 04:40:51'),(49,13,'lesson',NULL,'lesson','admin/lessonplan/lesson',4,'(\'lesson\', \'can_view\')',NULL,'lessonplan','lesson,editlesson','',1,'2022-09-15 00:30:55','2025-09-25 04:40:51'),(50,13,'topic',NULL,'topic','admin/lessonplan/topic',5,'(\'topic\', \'can_view\')',NULL,'lessonplan','topic,edittopic','',1,'2022-09-15 00:30:24','2025-09-25 04:40:51'),(51,14,'class_timetable',NULL,'class_timetable','admin/timetable/classreport',1,'(\'class_timetable\', \'can_view\')',NULL,'timetable','classreport,create','',1,'2022-07-22 22:01:22','2025-09-25 04:40:51'),(52,14,'teachers_timetable',NULL,'teachers_timetable','admin/timetable/mytimetable',2,'(\'teachers_time_table\', \'can_view\')',NULL,'timetable','mytimetable','',1,'2022-07-20 01:22:59','2025-09-25 04:40:51'),(53,14,'assign_class_teacher',NULL,'assign_class_teacher','admin/teacher/assign_class_teacher',3,'(\'assign_class_teacher\', \'can_view\')',NULL,'teacher','assign_class_teacher,update_class_teacher','',1,'2022-07-22 22:00:19','2025-09-25 04:40:51'),(54,14,'promote_students',NULL,'promote_students','admin/stdtransfer',4,'(\'promote_student\', \'can_view\')',NULL,'stdtransfer','index','',1,'2022-07-20 01:22:54','2025-09-25 04:40:51'),(55,14,'subject_group',NULL,'subject_group','admin/subjectgroup',5,'(\'subject_group\', \'can_view\')',NULL,'subjectgroup','index,edit','',1,'2022-07-22 21:59:42','2025-09-25 04:40:51'),(56,14,'subjects',NULL,'subjects','admin/subject',6,'(\'subject\', \'can_view\')',NULL,'subject','index,edit','',1,'2022-07-22 21:59:20','2025-09-25 04:40:51'),(57,14,'class',NULL,'class','classes',7,'(\'class\', \'can_view\')',NULL,'classes','index,edit','',1,'2022-07-22 21:58:49','2025-09-25 04:40:51'),(58,14,'sections',NULL,'sections','sections',8,'(\'section\', \'can_view\')',NULL,'sections','index,edit','',1,'2022-07-22 21:58:21','2025-09-25 04:40:51'),(59,15,'staff_directory',NULL,'staff_directory','admin/staff',1,'(\'staff\', \'can_view\')',NULL,'staff','index,edit,profile,create','',1,'2022-10-11 22:13:24','2025-09-25 04:40:51'),(60,15,'staff_attendance',NULL,'staff_attendance','admin/staffattendance',1,'(\'staff_attendance\', \'can_view\')',NULL,'staffattendance','index','',1,'2022-09-07 01:04:15','2025-09-25 04:40:51'),(61,15,'payroll',NULL,'payroll','admin/payroll',1,'(\'staff_payroll\', \'can_view\')',NULL,'payroll','index,edit,create','',1,'2022-08-16 00:58:44','2025-09-25 04:40:51'),(62,15,'approve_leave_request',NULL,'approve_leave_request','admin/leaverequest/leaverequest',1,'(\'approve_leave_request\', \'can_view\')',NULL,'leaverequest','leaverequest','',1,'2022-05-15 22:04:33','2025-09-25 04:40:51'),(74,15,'Apply OD/CPL Claim',NULL,'apply_leave_claim','admin/leaverequest/claimleave',1,'(\'apply_leave\', \'can_view\')',NULL,'leaverequest','claimleave','',1,'2022-05-15 22:11:41','2026-04-08 18:09:16'),(75,15,'leave_type',NULL,'leave_type','admin/leavetypes',1,'(\'leave_types\', \'can_view\')',NULL,'leavetypes','index,leaveedit,createleavetype','',1,'2022-10-18 00:19:22','2025-09-25 04:40:51'),(76,15,'teachers_rating',NULL,'teachers_rating','admin/staff/rating',1,'(\'teachers_rating\', \'can_view\')',NULL,'staff','rating','',1,'2022-05-15 22:15:31','2025-09-25 04:40:51'),(77,15,'department',NULL,'department','admin/department/department',1,'(\'department\', \'can_view\')',NULL,'department','department,departmentedit','',1,'2022-07-22 22:14:20','2025-09-25 04:40:51'),(78,15,'designation',NULL,'designation','admin/designation/designation',1,'(\'designation\', \'can_view\')',NULL,'designation','designation,designationedit','',1,'2022-07-22 22:15:04','2025-09-25 04:40:51'),(79,15,'disabled_staff',NULL,'disabled_staff','admin/staff/disablestafflist',1,'(\'disable_staff\', \'can_view\')',NULL,'staff','disablestafflist','',1,'2022-09-12 20:46:56','2025-09-25 04:40:51'),(80,16,'notice_board',NULL,'notice_board','admin/notification',1,'(\'notice_board\', \'can_view\')',NULL,'notification','index,edit,add','',1,'2022-07-22 22:17:24','2025-09-25 04:40:51'),(81,16,'send_email',NULL,'send_email','admin/mailsms/compose',2,'(\'email\', \'can_view\')',NULL,'mailsms','compose','',1,'2022-09-02 05:52:46','2025-09-25 04:40:51'),(82,16,'send_sms',NULL,'send_sms','admin/mailsms/compose_sms',3,'(\'sms\', \'can_view\')',NULL,'mailsms','compose_sms','',1,'2022-09-02 05:52:46','2025-09-25 04:40:51'),(83,16,'email_sms_log',NULL,'email_sms_log','admin/mailsms/index',4,'(\'email_sms_log\', \'can_view\')',NULL,'mailsms','index','',1,'2022-09-02 05:52:50','2025-09-25 04:40:51'),(84,16,'schedule_email_sms_log',NULL,'schedule_email_sms_log','admin/mailsms/schedule',5,'(\'schedule_email_sms_log\', \'can_view\')',NULL,'mailsms','schedule,edit_schedule','',1,'2022-09-12 20:07:38','2025-09-25 04:40:51'),(85,16,'login_credentials_send',NULL,'login_credentials_send','student/bulkmail',6,'(\'login_credentials_send\', \'can_view\')',NULL,'student','bulkmail','',1,'2022-09-02 05:52:46','2025-09-25 04:40:51'),(86,16,'email_template',NULL,'email_template','admin/mailsms/email_template',7,'(\'email_template\', \'can_view\')',NULL,'mailsms','email_template','',1,'2022-09-02 05:52:46','2025-09-25 04:40:51'),(87,16,'sms_template',NULL,'sms_template','admin/mailsms/sms_template',8,'(\'sms_template\', \'can_view\')',NULL,'mailsms','sms_template','',1,'2022-09-02 05:52:46','2025-09-25 04:40:51'),(88,17,'content_type',NULL,'content_type','admin/contenttype',3,'(\'content_type\', \'can_view\')',NULL,'contenttype','index,edit','',1,'2022-07-22 22:24:45','2025-09-25 04:40:51'),(89,17,'content_share_list',NULL,'content_share_list','admin/content/list',2,'(\'content_share_list\', \'can_view\')',NULL,'content','list','',1,'2022-07-21 23:07:17','2025-09-25 04:40:51'),(90,17,'upload_content',NULL,'upload_content','admin/content/upload',1,'(\'upload_content\', \'can_view\')',NULL,'content','upload','',1,'2022-07-21 23:07:17','2025-09-25 04:40:51'),(91,17,'video_tutorial',NULL,'video_tutorial','admin/video_tutorial',4,'(\'video_tutorial\', \'can_view\')',NULL,'video_tutorial','index','',1,'2022-07-21 23:07:17','2025-09-25 04:40:51'),(92,18,'add_homework',NULL,'add_homework','homework',1,'(\'homework\', \'can_view\')',NULL,'homework','index','',1,'2022-06-24 22:50:01','2025-09-25 04:40:51'),(93,18,'daily_assignment',NULL,'daily_assignment','homework/dailyassignment',2,'(\'daily_assignment\', \'can_view\')',NULL,'homework','dailyassignment','',1,'2022-07-22 22:27:23','2025-09-25 04:40:51'),(94,19,'book_list',NULL,'book_list','admin/book/getall',1,'(\'book_list\', \'can_view\')',NULL,'book','getall,index,edit,import,issue_returnreport','',1,'2022-09-07 00:45:50','2026-03-16 08:56:32'),(95,19,'issue_return',NULL,'issue_return','admin/member',2,'(\'issue_return\', \'can_view\')',NULL,'member','index,issue','',1,'2022-07-22 22:32:48','2025-09-27 07:27:42'),(96,19,'add_student',NULL,'add_student','admin/member/student',3,'(\'add_student\', \'can_view\')',NULL,'member','student','',1,'2022-05-16 00:22:54','2025-09-27 07:27:47'),(97,19,'add_staff_member',NULL,'add_staff_member','admin/member/teacher',4,'(\'add_staff_member\', \'can_view\')',NULL,'member','teacher','',1,'2022-05-16 00:31:43','2025-09-27 07:27:51'),(98,7,'search_expense',NULL,'search_expense','admin/expense/expensesearch',1,'(\'search_expense\', \'can_view\')',NULL,'expense','expensesearch','',1,'2022-05-16 00:36:09','2025-09-25 04:40:51'),(99,7,'expense_head',NULL,'expense_head','admin/expensehead',1,'(\'expense_head\', \'can_view\')',NULL,'expensehead','index,edit','',1,'2022-07-22 20:16:17','2025-09-25 04:40:51'),(100,20,'issue_item',NULL,'issue_item','admin/issueitem',7,'(\'issue_item\', \'can_view\')',10,'issueitem','index,create','',1,'2022-07-22 22:35:03','2026-03-16 06:47:53'),(101,20,'add_item_stock',NULL,'add_item_stock','admin/itemstock',6,'(\'item_stock\', \'can_view\')',10,'itemstock','index,edit','',1,'2022-07-22 22:36:17','2026-03-16 06:47:53'),(102,20,'item',NULL,'add_item','admin/item',2,'(\'item\', \'can_view\')',10,'item','index,edit','',1,'2022-07-22 22:36:56','2026-03-16 06:47:53'),(103,20,'item_category',NULL,'item_category','admin/itemcategory',3,'(\'item_category\', \'can_view\')',10,'itemcategory','index,edit','',1,'2022-07-22 22:37:12','2026-03-16 06:47:53'),(104,20,'item_store',NULL,'item_store','admin/itemstore',4,'(\'store\', \'can_view\')',10,'itemstore','index,edit,create','',1,'2022-09-16 00:49:03','2026-03-16 06:47:53'),(105,20,'item_supplier',NULL,'item_supplier','admin/itemsupplier',5,'(\'supplier\', \'can_view\')',10,'itemsupplier','index,edit,create','',1,'2022-07-22 22:38:22','2026-03-16 06:47:53'),(106,21,'fees_master',NULL,'fees_master','admin/transport/feemaster',1,'(\'transport_fees_master\', \'can_view\')',NULL,'transport','feemaster','',1,'2023-03-30 18:33:14','2025-09-25 04:40:51'),(107,21,'pickup_point',NULL,'pickup_point','admin/pickuppoint',1,'(\'pickup_point\', \'can_view\')',NULL,'pickuppoint','index','',1,'2023-03-30 18:24:24','2025-09-25 04:40:51'),(108,21,'routes',NULL,'routes','admin/route',1,'(\'routes\', \'can_view\')',NULL,'route','index,edit','',1,'2022-09-16 19:21:23','2025-09-25 04:40:51'),(109,21,'vehicles',NULL,'vehicles','admin/vehicle',1,'(\'vehicle\', \'can_view\')',NULL,'vehicle','index','',1,'2022-05-16 01:29:35','2025-09-25 04:40:51'),(110,21,'assign_vehicle',NULL,'assign_vehicle','admin/vehroute',1,'(\'assign_vehicle\',\'can_view\')',NULL,'vehroute','index,edit','',1,'2022-10-18 20:06:08','2025-09-25 04:40:51'),(111,21,'route_pickup_point',NULL,'route_pickup_point','admin/pickuppoint/assign',1,'(\'route_pickup_point\', \'can_view\')',NULL,'pickuppoint','assign','',1,'2023-03-30 18:25:08','2025-09-25 04:40:51'),(112,21,'student_transport_fees',NULL,'student_transport_fees','admin/pickuppoint/student_fees',1,'(\'student_transport_fees\', \'can_view\')',NULL,'pickuppoint','student_fees','',1,'2023-03-30 18:25:43','2025-09-25 04:40:51'),(113,22,'hostel_rooms',NULL,'hostel_rooms','admin/hostelroom',1,'(\'hostel_rooms\', \'can_view\')',NULL,'hostelroom','index,edit','',1,'2022-07-22 23:27:48','2025-09-25 04:40:51'),(114,22,'room_type',NULL,'room_type','admin/roomtype',2,'(\'room_type\', \'can_view\')',NULL,'roomtype','index,edit','',1,'2022-07-22 23:32:14','2025-09-25 04:40:51'),(115,22,'hostel',NULL,'hostel','admin/hostel',3,'(\'hostel\', \'can_view\')',NULL,'hostel','index,edit','',1,'2022-07-22 23:32:39','2025-09-25 04:40:51'),(116,23,'student_certificate',NULL,'student_certificate','admin/certificate',1,'(\'student_certificate\', \'can_view\')',NULL,'certificate','index,edit','',1,'2022-07-22 23:44:30','2025-09-25 04:40:51'),(117,23,'generate_certificate',NULL,'generate_certificate','admin/generatecertificate',1,'(\'generate_certificate\', \'can_view\')',NULL,'generatecertificate','index,search','',1,'2022-07-22 23:46:16','2025-09-25 04:40:51'),(118,23,'student_id_card',NULL,'student_id_card','admin/studentidcard',1,'(\'student_id_card\', \'can_view\')',NULL,'studentidcard','index,edit','',1,'2022-07-22 23:47:01','2025-09-25 04:40:51'),(119,23,'generate_id_card',NULL,'generate_id_card','admin/generateidcard/search',1,'(\'generate_id_card\', \'can_view\')',NULL,'generateidcard','search','',1,'2022-05-17 18:35:13','2025-09-25 04:40:51'),(120,23,'staff_id_card',NULL,'staff_id_card','admin/staffidcard',1,'(\'staff_id_card\', \'can_view\')',NULL,'staffidcard','index,edit','',1,'2022-07-22 23:48:13','2025-09-25 04:40:51'),(121,23,'generate_staff_id_card',NULL,'generate_staff_id_card','admin/generatestaffidcard',1,'(\'generate_staff_id_card\', \'can_view\')',NULL,'generatestaffidcard','index,search','',1,'2022-07-22 23:49:06','2025-09-25 04:40:51'),(122,24,'event',NULL,'event','admin/front/events',1,'(\'event\', \'can_view\')',NULL,'events','index,edit,create','',1,'2022-07-22 23:51:51','2025-09-25 04:40:51'),(123,24,'gallery',NULL,'gallery','admin/front/gallery',1,'(\'gallery\', \'can_view\')',NULL,'gallery','index,edit,create','',1,'2022-07-22 23:52:22','2025-09-25 04:40:51'),(124,24,'news',NULL,'news','admin/front/notice',1,'(\'notice\', \'can_view\')',NULL,'notice','index,edit,create','',1,'2022-07-22 23:54:23','2025-09-25 04:40:51'),(125,24,'media_manager',NULL,'media_manager','admin/front/media',1,'(\'media_manager\', \'can_view\')',NULL,'media','index','',1,'2022-05-17 19:03:32','2025-09-25 04:40:51'),(126,24,'pages',NULL,'pages','admin/front/page',1,'(\'pages\', \'can_view\')',NULL,'page','index,edit,create','',1,'2022-07-22 23:55:28','2025-09-25 04:40:51'),(127,24,'menus',NULL,'menus','admin/front/menus',1,'(\'menus\', \'can_view\')',NULL,'menus','index,additem','',1,'2022-07-22 23:56:31','2025-09-25 04:40:51'),(128,24,'banner_images',NULL,'banner_images','admin/front/banner',1,'(\'banner_images\', \'can_view\')',NULL,'banner','index','',1,'2022-05-17 19:10:53','2025-09-25 04:40:51'),(129,25,'manage_alumini',NULL,'manage_alumini','admin/alumni/alumnilist',1,'(\'manage_alumni\', \'can_view\')',NULL,'alumni','alumnilist','',1,'2022-07-22 23:58:36','2025-09-25 04:40:51'),(130,25,'events',NULL,'events','admin/alumni/events',1,'(\'events\', \'can_view\')',NULL,'alumni','events','',1,'2022-07-22 23:59:09','2025-09-25 04:40:51'),(131,26,'student_information',NULL,'student_information','report/studentinformation',1,'(\'student_report\', \'can_view\') || (\'guardian_report\', \'can_view\') || (\'student_history\', \'can_view\') || (\'student_login_credential_report\', \'can_view\') || (\'class_subject_report\', \'can_view\') || (\'admission_report\', \'can_view\') || (\'sibling_report\', \'can_view\') || (\'homehork_evaluation_report\', \'can_view\') || (\'student_profile\', \'can_view\') || (\'student_gender_ratio_report\', \'can_view\') || (\'student_teacher_ratio_report\', \'can_view\')',NULL,'report','studentinformation,studentreport,online_admission_report,student_teacher_ratio,boys_girls_ratio,student_profile,sibling_report,admission_report,class_subject,classsectionreport,guardianreport,admissionreport,logindetailreport,parentlogindetailreport','',1,'2022-09-25 18:26:53','2025-09-25 04:40:51'),(132,26,'finance',NULL,'finance','financereports/finance',2,'(\'fees_statement\', \'can_view\') || (\'balance_fees_report\', \'can_view\') || (\'fees_collection_report\', \'can_view\') || (\'online_fees_collection_report\', \'can_view\') || (\'income_report\', \'can_view\') || (\'expense_report\', \'can_view\') || (\'payroll_report\', \'can_view\') || (\'income_group_report\', \'can_view\') || (\'expense_group_report\', \'can_view\') || (\'online_admission\', \'can_view\')',NULL,'financereports','finance,reportduefees,reportdailycollection,reportbyname,studentacademicreport,collection_report,onlinefees_report,duefeesremark,income,expense,payroll,incomegroup,expensegroup,onlineadmission','',1,'2022-09-24 01:20:32','2025-09-25 04:40:51'),(133,26,'attendance',NULL,'attendance','attendencereports/attendance',3,'(\'attendance_report\', \'can_view\') || (\'student_attendance_type_report\', \'can_view\') || (\'daily_attendance_report\', \'can_view\') || (\'staff_attendance_report\', \'can_view\')',NULL,'attendencereports','attendance,classattendencereport,attendancereport,daily_attendance_report,staffattendancereport,biometric_attlog,reportbymonthstudent,reportbymonth','',1,'2022-09-26 00:36:08','2025-09-25 04:40:51'),(134,26,'examinations',NULL,'examinations','admin/examresult/examinations',4,'(\'rank_report\', \'can_view\')',NULL,'examresult','rankreport,examinations','',1,'2022-09-19 21:34:13','2025-09-25 04:40:51'),(135,26,'lesson_plan',NULL,'lesson_plan','report/lesson_plan',6,'(\'syllabus_status_report\', \'can_view\') || (\'teacher_syllabus_status_report\', \'can_view\')',NULL,'report','lesson_plan,teachersyllabusstatus','',1,'2022-07-25 00:39:17','2025-09-25 04:40:51'),(136,26,'human_resource',NULL,'human_resource','report/human_resource',7,'(\'staff_report\', \'can_view\') || (\'payroll_report\', \'can_view\')',NULL,'report','human_resource,staff_report,payrollreport','',1,'2022-07-25 00:38:20','2025-09-25 04:40:51'),(137,26,'library',NULL,'library','report/library',9,'(\'book_issue_report\', \'can_view\') || (\'book_due_report\', \'can_view\') || (\'book_issue_return_report\', \'can_view\') || (\'book_inventory_report\', \'can_view\')',NULL,'report','library,studentbookissuereport,bookduereport,bookinventory','',1,'2022-09-07 00:53:15','2025-09-25 04:40:51'),(138,26,'inventory',NULL,'inventory','report/inventory',10,'(\'stock_report\', \'can_view\') || (\'add_item_report\', \'can_view\') || (\'issue_item_report\', \'can_view\')',NULL,'report','inventory,inventorystock,additem,issueinventory','',1,'2022-07-25 00:30:57','2025-09-25 04:40:51'),(139,26,'hostel',NULL,'hostel','admin/hostelroom/studenthosteldetails',12,'(\'hostel_report\', \'can_view\')',NULL,'hostelroom','studenthosteldetails','',1,'2022-07-20 01:30:07','2025-09-25 04:40:51'),(140,26,'alumni',NULL,'alumni','report/alumnireport',13,'(\'alumni_report\', \'can_view\')',NULL,'report','alumnireport','',1,'2022-07-20 01:30:07','2025-09-25 04:40:51'),(141,26,'user_log',NULL,'user_log','admin/userlog',14,'(\'user_log\', \'can_view\')',NULL,'userlog','index','',1,'2022-07-20 01:30:07','2025-09-25 04:40:51'),(142,26,'audit_trail_report',NULL,'audit_trail_report','admin/audit',15,'(\'audit_trail_report\', \'can_view\')',NULL,'audit','index','',1,'2022-07-20 01:30:07','2025-09-25 04:40:51'),(143,26,'online_examinations',NULL,'online_examinations','admin/onlineexam/report',5,'(\'online_exam_wise_report\', \'can_view\') || (\'online_exams_report\', \'can_view\') || (\'online_exams_attempt_report\', \'can_view\') || (\'online_exams_rank_report\', \'can_view\')',NULL,'onlineexam','report,onlineexams','',1,'2022-07-25 00:48:23','2025-09-25 04:40:51'),(144,26,'homework',NULL,'homework','homework/homeworkordailyassignmentreport',8,'(\'homework\', \'can_view\') || (\'daily_assignment\', \'can_view\')',NULL,'homework','homeworkordailyassignmentreport,homeworkreport,evaluation_report,dailyassignmentreport','',1,'2022-09-20 22:28:47','2025-09-25 04:40:51'),(145,26,'transport',NULL,'transport','admin/route/studenttransportdetails',11,'(\'transport_report\', \'can_view\')',NULL,'route','studenttransportdetails','',1,'2022-07-20 01:30:07','2025-09-25 04:40:51'),(146,27,'general_setting',NULL,'general_setting','schsettings',1,'(\'general_setting\', \'can_view\')',NULL,'schsettings','index,logo,miscellaneous,backendtheme,mobileapp,studentguardianpanel,fees,idautogeneration,attendancetype,maintenance,whatsappsettings','',1,'2025-02-12 22:03:12','2025-09-25 04:40:51'),(147,27,'session_setting',NULL,'session_setting','sessions',2,'(\'session_setting\', \'can_view\')',NULL,'sessions','index,edit','',1,'2022-07-23 00:57:16','2025-09-25 04:40:51'),(148,27,'notification_setting',NULL,'notification_setting','admin/notification/setting',3,'(\'notification_setting\', \'can_view\')',NULL,'notification','setting','',1,'2022-07-07 21:12:28','2025-09-25 04:40:51'),(149,27,'sms_setting',NULL,'sms_setting','smsconfig',4,'(\'sms_setting\', \'can_view\')',NULL,'smsconfig','index','',1,'2022-07-07 21:12:28','2025-09-25 04:40:51'),(150,27,'email_setting',NULL,'email_setting','emailconfig',5,'(\'email_setting\', \'can_view\')',NULL,'emailconfig','index','',1,'2022-07-07 21:12:28','2025-09-25 04:40:51'),(151,27,'payment_methods',NULL,'payment_methods','admin/paymentsettings',6,'(\'payment_methods\', \'can_view\')',NULL,'paymentsettings','index','',1,'2022-07-07 21:12:28','2025-09-25 04:40:51'),(152,27,'print_headerfooter',NULL,'print_headerfooter','admin/print_headerfooter',7,'(\'print_header_footer\', \'can_view\')',NULL,'print_headerfooter','index','',1,'2022-07-07 21:12:28','2025-09-25 04:40:51'),(153,27,'front_cms_setting',NULL,'front_cms_setting','admin/frontcms',8,'(\'front_cms_setting\', \'can_view\')',NULL,'frontcms','index','',1,'2022-07-07 21:12:28','2025-09-25 04:40:51'),(154,27,'roles_permissions',NULL,'roles_permissions','admin/roles',9,'(\'superadmin\', \'can_view\')',NULL,'roles','index,permission','',1,'2022-09-09 00:03:34','2025-09-25 04:40:51'),(155,27,'backup_restore',NULL,'backup_restore','admin/admin/backup',10,'(\'backup\', \'can_view\')',NULL,'admin','backup','',1,'2022-07-07 21:12:28','2025-09-25 04:40:51'),(156,27,'users',NULL,'users','admin/users',13,'(\'user_status\', \'can_view\')',NULL,'users','index','',1,'2022-07-20 01:34:09','2025-09-25 04:40:51'),(157,27,'languages',NULL,'languages','admin/language',11,'(\'languages\', \'can_view\')',NULL,'language','index,create','',1,'2022-09-09 22:14:52','2025-09-25 04:40:51'),(158,27,'modules',NULL,'modules','admin/module',14,'(\'superadmin\', \'can_view\')',NULL,'module','index','',1,'2022-07-20 01:34:06','2025-09-25 04:40:51'),(159,27,'custom_fields',NULL,'custom_fields','admin/customfield',15,'(\'custom_fields\', \'can_view\')',NULL,'customfield','index,edit','',1,'2022-07-23 01:02:14','2025-09-25 04:40:51'),(160,27,'captcha_setting',NULL,'captcha_setting','admin/captcha',16,'(\'superadmin\', \'can_view\')',NULL,'captcha','index','',1,'2022-07-20 01:34:06','2025-09-25 04:40:51'),(161,27,'system_fields',NULL,'system_fields','admin/systemfield',17,'(\'system_fields\', \'can_view\')',NULL,'systemfield','index','',1,'2022-07-21 19:07:38','2025-09-25 04:40:51'),(162,27,'student_profile_update',NULL,'student_profile_update','student/profilesetting',18,'(\'student_profile_update\', \'can_view\')',NULL,'student','profilesetting','',1,'2022-07-20 01:34:06','2025-09-25 04:40:51'),(163,27,'online_admission',NULL,'online_admission','admin/onlineadmission/admissionsetting',19,'(\'online_admission\', \'can_view\')',NULL,'onlineadmission','admissionsetting','',1,'2022-07-20 01:34:06','2026-02-25 13:13:36'),(164,27,'file_types',NULL,'file_types','admin/admin/filetype',20,'(\'superadmin\', \'can_view\')',NULL,'admin','filetype','',1,'2022-07-20 01:34:30','2025-09-25 04:40:51'),(166,27,'sidebar_menu',NULL,'sidebar_menu','admin/sidemenu',21,'(\'sidebar_menu\', \'can_view\')',NULL,'sidemenu','index','',1,'2022-10-13 00:49:51','2025-09-25 04:40:51'),(175,30,'live_class',NULL,'live_class','admin/conference/timetable',2,'(\'live_classes\', \'can_view\')',NULL,'conference','timetable','sszlc',1,'2022-07-22 05:55:48','2026-03-06 19:05:29'),(176,30,'live_meeting',NULL,'live_meeting','admin/conference/meeting',1,'(\'live_meeting\', \'can_view\')',NULL,'conference','meeting','sszlc',1,'2022-07-22 05:55:48','2026-03-06 19:05:29'),(177,30,'live_class_report',NULL,'live_class_report','admin/conference/class_report',3,'(\'live_classes_report\', \'can_view\')',NULL,'conference','class_report','sszlc',1,'2022-07-11 11:38:41','2026-03-06 19:05:29'),(178,30,'live_meeting_report',NULL,'live_meeting_report','admin/conference/meeting_report',4,'(\'live_meeting_report\', \'can_view\')',NULL,'conference','meeting_report','sszlc',1,'2022-07-11 11:38:41','2026-03-06 19:05:29'),(179,30,'setting',NULL,'setting','admin/conference',5,'(\'setting\', \'can_view\')',NULL,'conference','index','',1,'2022-07-11 11:38:41','2026-03-06 19:05:29'),(181,3,'fees_discount',NULL,'fees_discount','admin/feediscount',8,'(\'fees_discount\', \'can_view\')',NULL,'feediscount','index,edit,assign','',1,'2022-08-07 19:03:27','2025-09-25 04:40:51'),(182,3,'fees_carry_forward',NULL,'fees_carry_forward','admin/feesforward',9,'(\'fees_carry_forward\', \'can_view\')',NULL,'feesforward','index','',1,'2022-08-07 19:03:24','2025-09-25 04:40:51'),(183,3,'fees_reminder',NULL,'fees_reminder','admin/feereminder/setting',10,'(\'fees_reminder\', \'can_view\')',NULL,'feereminder','setting','',1,'2022-08-07 19:03:21','2025-09-25 04:40:51'),(184,27,'currency',NULL,'currency','admin/currency',12,'(\'currency\', \'can_view\')',NULL,'currency','index','',1,'2022-07-20 01:34:09','2025-09-25 04:40:51'),(190,3,'offline_bank_payments',NULL,'offline_bank_payments','admin/offlinepayment',2,'(\'offline_bank_payments\', \'can_view\')',NULL,'offlinepayment','index','',1,'2022-08-07 19:05:29','2025-09-25 04:40:51'),(191,13,'Copy Old Lessons',NULL,'copy_old_lesson','admin/lessonplan/copylesson',1,'(\'copy_old_lesson\', \'can_view\')',NULL,'lessonplan','copylesson',NULL,1,'2022-09-08 23:20:37','2025-09-25 04:40:51'),(192,10,'Period Attendance','period_attendance','period_attendance','admin/subjectattendence/index',4,'(\'student_attendance\',\'can_view\')',NULL,'subjectattendence','index',NULL,1,'2022-10-19 18:50:25','2025-10-26 17:37:07'),(193,10,'Period Attendance By Date','period_attendance_by_date','period_attendance_by_date','admin/subjectattendence/reportbydate',5,'(\'attendance_by_date\', \'can_view\')',NULL,'subjectattendence','reportbydate',NULL,1,'2022-10-19 18:50:25','2025-10-26 17:37:07'),(198,33,'overview',NULL,'overview','admin/multibranch/branch/overview',1,'(\'multi_branch_overview\', \'can_view\')',NULL,'branch','overview','',1,'2022-11-15 00:35:27','2025-11-08 02:02:02'),(199,33,'report',NULL,'report','admin/multibranch/finance/index',1,'(\'multi_branch_daily_collection_report\', \'can_view\') || (\'multi_branch_payroll\', \'can_view\') || (\'multi_branch_income_report\', \'can_view\') || (\'multi_branch_expense_report\', \'can_view\') || (\'multi_branch_user_log_report\', \'can_view\')',NULL,'finance','dailycollectionreport,payroll,incomelist,expenselist,incomereport,expensereport,userlogreport,index','',1,'2022-12-22 00:29:38','2025-11-08 02:02:02'),(200,33,'setting',NULL,'setting','admin/multibranch/branch',1,'(\'multi_branch_setting\', \'can_view\')',NULL,'branch','index','',1,'2022-11-15 00:15:32','2025-11-08 01:23:13'),(201,34,'exam',NULL,'exam','cbseexam/exam',1,'(\'cbse_exam\', \'can_view\')',NULL,'exam','index,examwiserank','sscbse',1,'2023-07-04 09:57:01','2026-03-05 21:29:42'),(202,34,'exam_schedule',NULL,'exam_schedule','cbseexam/exam/examtimetable',2,'(\'cbse_exam_schedule\', \'can_view\')',NULL,'exam','examtimetable','sscbse',1,'2023-07-04 13:01:15','2026-03-05 21:29:46'),(203,34,'print_marksheet',NULL,'print_marksheet','cbseexam/result/marksheet',3,'(\'cbse_exam_print_marksheet\', \'can_view\')',NULL,'result','marksheet','sscbse',1,'2023-05-25 05:23:50','2026-03-05 21:29:46'),(205,34,'assign_observation',NULL,'assign_observation','cbseexam/observation/assign',5,'(\'cbse_exam_assign_observation\', \'can_view\')',NULL,'observation','assign','sscbse',1,'2023-05-25 05:31:49','2026-03-05 21:29:46'),(210,34,'template',NULL,'template','cbseexam/template',4,'(\'cbse_exam_template\', \'can_view\')',NULL,'template','index,templatewiserank','sscbse',1,'2023-07-04 09:57:06','2026-03-05 21:29:46'),(211,34,'reports',NULL,'reports','cbseexam/report/index',7,'(\'subject_marks_report\', \'can_view\') || (\'template_marks_report\', \'can_view\')',NULL,'report','index,templatewise,examsubject','sscbse',1,'2023-07-01 05:22:34','2026-03-05 21:29:46'),(212,34,'setting',NULL,'setting','cbseexam/cbsecategory/index',8,'(\'cbse_exam_setting\', \'can_view\') || (\'cbse_exam_category\', \'can_view\') || (\'cbse_exam_term\', \'can_view\') || (\'cbse_exam_assessment\', \'can_view\') || (\'cbse_exam_grade\', \'can_view\')',NULL,'cbsecategory','index','',1,'2023-07-03 05:26:03','2026-03-05 21:29:46'),(215,36,'annual_calendar',NULL,'annual_calendar','admin/holiday/index',1,'(\'annual_calendar\', \'can_view\')',NULL,'holiday','index','',1,'2024-10-14 01:07:58','2025-09-25 04:40:51'),(216,36,'holiday_type',NULL,'holiday_type','admin/holiday/holidaytype',1,'(\'holiday_type\', \'can_view\')',NULL,'holiday','holidaytype,editholidaytype','',1,'2024-10-14 01:06:02','2025-09-25 04:40:51'),(217,37,'download_cv',NULL,'download_cv','admin/resume/download',2,'(\'download_cv\', \'can_view\')',NULL,'resume','download',NULL,1,'2025-01-08 21:05:11','2025-09-25 04:40:51'),(218,37,'build_cv',NULL,'build_cv','admin/resume/index',1,'(\'build_cv\', \'can_view\')',NULL,'resume','index,resume_setting,student_resume_details',NULL,1,'2024-12-06 00:42:02','2025-09-25 04:40:51'),(219,27,'addons',NULL,'addons','admin/addons',13,'(\'superadmin\', \'can_view\')',NULL,'addons','index','',1,'2024-12-21 00:43:48','2025-09-25 04:40:51'),(225,34,'print_admit_card',NULL,'admit_card','cbseexam/cbseadmitcard/admitcard',6,'(\'cbse_exam_print_admit_card\', \'can_view\') || (\'cbse_exam_admit_card\', \'can_view\')',NULL,'cbseadmitcard','admitcard,index,edit','',1,'2023-07-03 03:26:03','2025-10-04 06:41:16'),(226,27,'whatsapp_messaging',NULL,'whatsapp_messaging','whatsappconfig/index',4,'(\'whatsapp_messaging\', \'can_view\')',NULL,'whatsappconfig','index','',1,'2025-01-10 11:08:46','2025-10-04 06:41:16'),(228,19,'category_master','librarycategory','category_master','admin/librarycategory',7,'(\'library_category\', \'can_view\')',9,'librarycategory','index,create,edit,delete','',1,'2025-09-26 02:15:40','2025-09-27 21:33:13'),(229,19,'Sub Category','librarysubcategory','sub_category_master','admin/librarysubcategory',7,'(\'library_subcategory\', \'can_view\')',9,'librarysubcategory','index,create,edit,delete','',1,'2025-09-26 02:15:40','2025-09-27 07:28:07'),(230,19,'Publisher Master','librarypublisher','publisher_master','admin/librarypublisher',8,'(\'library_publisher\', \'can_view\')',9,'librarypublisher','index,create,edit,delete','',1,'2025-09-26 02:15:40','2025-09-27 07:28:10'),(231,19,'Vendor Master','libraryvendor','vendor_master','admin/libraryvendor',9,'(\'library_vendor\', \'can_view\')',9,'libraryvendor','index,create,edit,delete','',1,'2025-09-26 02:15:40','2025-09-27 07:28:13'),(232,19,'Book Type Master','librarybooktype','book_type_master','admin/librarybooktype',10,'(\'library_book_type\', \'can_view\')',9,'librarybooktype','index,create,edit,delete','',1,'2025-09-26 02:15:40','2025-09-27 07:28:17'),(233,19,'Subject Master','librarysubject','subject_master','admin/librarysubject',11,'(\'library_subject\', \'can_view\')',9,'librarysubject','index,create,edit,delete','',1,'2025-09-26 02:15:40','2025-09-27 07:28:21'),(234,19,'Position Rack Master','librarypositionrack','position_rack_master','admin/librarypositionrack',12,'(\'library_position_rack\', \'can_view\')',9,'librarypositionrack','index,create,edit,delete','',1,'2025-09-26 02:15:40','2025-09-27 07:28:24'),(235,19,'Position Shelf Master','librarypositionshelf','position_shelf_master','admin/librarypositionshelf',13,'(\'library_position_shelf\', \'can_view\')',9,'librarypositionshelf','index,create,edit,delete','',1,'2025-09-26 02:15:40','2025-09-27 07:28:26'),(236,19,'OPAQ','opaq','opaq','admin/opaq',5,'(\'library_opac\', \'can_view\')',9,'opaq','index','',1,'2025-09-26 02:15:40','2026-03-16 08:56:32'),(237,19,'CheckIn - CheckOut','library_checkin_checkout','library_checkin_checkout','admin/library_checkin_checkout',6,'(\'library_checkin_checkout\', \'can_view\')',9,'library_checkin_checkout','index,create,edit,delete','',1,'2025-09-26 02:15:40','2025-09-27 07:28:04'),(238,19,'Checkout - Pendings','library_checkout_pending','library_checkout_pending','admin/library_checkout_pending',6,'(\'library_checkout_pending\', \'can_view\')',9,'library_checkout_pending','index,create,edit,delete','',1,'2025-09-26 02:15:40','2025-09-27 07:28:04'),(239,2,'birthday_list',NULL,'birthday_list','admin/birthday_list',9,'(\'birthday_list\', \'can_view\')',NULL,'birthday','birthday_list',NULL,1,'2022-07-22 19:50:41','2025-10-06 17:02:08'),(240,38,'Hall Master',NULL,'hall_master','admin/hall/hall_master',1,'(\'hall_master\', \'can_view\')',NULL,'hall','hall_master,add,edit','',1,'2022-07-22 22:17:24','2025-10-06 10:42:30'),(241,38,'Hall Bookings',NULL,'hall_bookings','admin/hall/hall_bookings',2,'(\'hall_bookings\', \'can_view\')',NULL,'hall','hall_bookings,approve_booking,reject_booking','',1,'2022-07-22 22:17:24','2025-10-06 10:44:02'),(242,38,'Approval Bookings',NULL,'approval_configuration','admin/hall/approval_configuration',3,'(\'approval_configuration\', \'can_view\')',NULL,'hall','approval_configuration','',1,'2022-07-22 22:17:24','2025-10-06 10:44:39'),(243,39,'naac_configuration',NULL,'naac_configuration','naac/configuration',1,'(\'configuration\', \'can_view\')',NULL,'naac','configuration','',1,'2022-07-22 20:12:25','2025-10-22 22:13:14'),(244,39,'naac_iiqa',NULL,'naac_iiqa','naac/iiqa',1,'(\'iiqa\', \'can_view\')',NULL,'naac','iiqa','',1,'2022-07-22 20:12:25','2025-10-22 22:12:41'),(245,39,'naac_ssr',NULL,'naac_ssr','naac/ssr',1,'(\'ssr\', \'can_view\')',NULL,'naac','ssr','',1,'2022-07-22 20:12:25','2025-10-22 22:12:48'),(246,39,'naac_aqar',NULL,'naac_aqar','naac/aqar',1,'(\'aqar\', \'can_view\')',NULL,'naac','aqar','',1,'2022-07-22 20:12:25','2025-10-22 22:13:21'),(247,15,'manage_biometric_device',NULL,'manage_biometric_device','admin/staff/managebiometricdevice',1,'(\'manage_biometric_device\', \'can_view\')',NULL,'staff','managebiometricdevice','',1,'2022-09-12 20:46:56','2025-09-25 04:40:51'),(248,3,'incidental_fee_type',NULL,'incidental_fee_type','admin/incidental_fee_type',7,'(\'incidental_fee_type\', \'can_view\')',NULL,'incidental_fee_type','index,edit','',1,'2022-08-07 19:03:29','2025-11-03 04:41:12'),(249,3,'assign_incidental_fee',NULL,'assign_incidental_fee','admin/assign_incidental_fee',7,'(\'assign_incidental_fee\', \'can_view\')',NULL,'assign_incidental_fee','index','',1,'2022-08-07 19:03:29','2025-11-03 04:42:32'),(250,3,'collect_incidental_fee',NULL,'collect_incidental_fee','admin/collect_incidental_fee',7,'(\'collect_incidental_fee\', \'can_view\')',NULL,'collect_incidental_fee','index,searchStudent,receipt','',1,'2022-08-07 19:03:29','2025-11-03 04:42:04'),(251,21,'student_transport_assign_route',NULL,'student_transport_assign_route','admin/assign_transport_fee/index',1,'(\'student_transport_assign_route\', \'can_view\')',NULL,'assignpickuppoint','student_transport_assign_route','',1,'2023-03-30 18:25:43','2025-11-14 04:23:45'),(252,15,'Recommender Leave Requests',NULL,'recommender_leave_requests','admin/leaverequest/recommender_leave_requests',1,'(\'recommender_leave_requests\', \'can_view\')',NULL,'leaverequest','recommender_leave_requests','',1,'2022-09-13 02:16:56','2025-09-25 10:10:51'),(255,15,'Special Attendance','special_attendance','special_attendance','admin/specialattendance/index',0,'(\"special_attendance\", \"can_view\")',NULL,'specialattendance','index',NULL,1,'2026-02-04 22:05:46','2026-02-04 22:05:46'),(256,15,'Initial Leave Balance',NULL,'initial_leave_balance','admin/leave_balance_setup/index',NULL,'(\'initial_leave_balance\', \'can_view\')',NULL,'leave_balance_setup','index,ajax_save_balances,ajax_get_staff_balances',NULL,1,'2026-02-10 18:20:47','2026-02-10 18:20:47'),(257,15,'Attendance Exceptions',NULL,'attendance_exceptions','admin/attendance_exceptions/index',NULL,'(\'attendance_exceptions\', \'can_view\')',NULL,'attendance_exceptions','index,resolve,get_punch_context',NULL,1,'2026-02-10 18:48:06','2026-02-10 18:48:06'),(258,27,'Set Final Years',NULL,'set_final_years','admin/finalyearclasses',1,'(\'final_year_classes\', \'can_view\')',NULL,'finalyearclasses','index,save','',1,'2026-02-16 17:20:06','2026-02-16 17:20:06'),(259,20,'inventory_dashboard','inventory_dashboard','inventory_dashboard','admin/inventorydashboard',1,'(\'inventory_dashboard\', \'can_view\')',10,'inventorydashboard','index',NULL,1,'2026-03-13 18:00:31','2026-03-16 06:47:53'),(260,20,'inventory_indents','inventory_indents','inventory_indents','admin/inventoryindent',8,'(\'inventory_indents\', \'can_view\')',10,'inventoryindent','index',NULL,1,'2026-03-13 18:00:31','2026-03-16 06:47:53'),(261,20,'indent_approvals','indent_approvals','indent_approvals','admin/inventoryindent/approvals',9,'(\'indent_approvals\', \'can_view\')',10,'inventoryindent','approvals',NULL,1,'2026-03-13 18:00:31','2026-03-16 06:47:53'),(262,20,'purchase_orders','purchase_orders','purchase_orders','admin/inventoryprocurement/purchaseorders',11,'(\'purchase_orders\', \'can_view\')',10,'inventoryprocurement','purchaseorders',NULL,1,'2026-03-13 18:00:31','2026-03-16 06:47:53'),(263,20,'goods_receipts','goods_receipts','goods_receipts','admin/inventoryprocurement/goodsreceipts',12,'(\'goods_receipts\', \'can_view\')',10,'inventoryprocurement','goodsreceipts',NULL,1,'2026-03-13 18:00:31','2026-03-16 06:47:53'),(264,20,'asset_register','asset_register','asset_register','admin/assetmanagement/register',13,'(\'asset_register\', \'can_view\')',10,'assetmanagement','register',NULL,1,'2026-03-13 18:00:31','2026-03-16 06:47:53'),(265,20,'asset_assignment','asset_assignment','asset_assignment','admin/assetmanagement/assignment',14,'(\'asset_assignment\', \'can_view\')',10,'assetmanagement','assignment',NULL,1,'2026-03-13 18:00:31','2026-03-16 06:47:53'),(266,20,'asset_transfer','asset_transfer','asset_transfer','admin/assetmanagement/transfer',15,'(\'asset_transfer\', \'can_view\')',10,'assetmanagement','transfer',NULL,1,'2026-03-13 18:00:31','2026-03-16 06:47:53'),(267,20,'asset_maintenance','asset_maintenance','asset_maintenance','admin/assetmanagement/maintenance',16,'(\'asset_maintenance\', \'can_view\')',10,'assetmanagement','maintenance',NULL,1,'2026-03-13 18:00:31','2026-03-16 06:47:53'),(268,20,'po_approvals','po_approvals','po_approvals','admin/inventoryprocurement/poapprovals',10,'(\'po_approvals\', \'can_view\')',10,'inventoryprocurement','poapprovals',NULL,1,'2026-03-13 18:30:19','2026-03-16 06:47:53'),(269,15,'apply_leave',NULL,'apply_leave','admin/leaverequest/applyleave',1,'(\'apply_leave\', \'can_view\')',NULL,'leaverequest','applyleave',NULL,1,'2026-03-28 00:36:21','2026-04-12 07:34:08'),(270,15,'update_leave_balance','update_leave_balance','update_leave_balance','admin/update_leave_balance/index',NULL,'(\'update_leave_balance\', \'can_view\')',NULL,'update_leave_balance','index',NULL,1,'2026-04-05 10:22:12','2026-04-12 07:34:08');
/*!40000 ALTER TABLE `sidebar_sub_menus` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `source`
--

LOCK TABLES `source` WRITE;
/*!40000 ALTER TABLE `source` DISABLE KEYS */;
INSERT INTO `source` VALUES (1,'Walk-in',''),(2,'Website',''),(3,'Social Media(FB, Insta, LinkedIn)',''),(4,'Education Fair',''),(5,'Education Fair(Dharmapuri)','Education fair - vijay info'),(6,'Education Fair(Chennai)','Education fair in chennai'),(7,'Education Fair(Vellor)','Education Fair(Dinamalar)'),(8,'Education Fair(ChennaiTradecenter)','Education Fair(ChennaiTradecenter)'),(9,'Education Fair(Hosur)',''),(10,'Education Fair(Tirupathur)',''),(11,'Education Fair(Nagercoil)','Education Fair(Nagercoil)'),(12,'Education Fair(Thiruvannamalai)',''),(13,'Education Fair(Tuticorin)',''),(14,'Education Fair(Cuddalore)',''),(15,'Education Fair(Pondycherry)',''),(16,'Education Fair(Tiruvarur)',''),(17,'Education Fair(Tirunelveli)',''),(18,'Education Fair(Kumbakonam)',''),(19,'Education Fair(News24/7)','Educational fair at trade center 8th and 9 th april 2026');
/*!40000 ALTER TABLE `source` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `staff_attendance_type`
--

LOCK TABLES `staff_attendance_type` WRITE;
/*!40000 ALTER TABLE `staff_attendance_type` DISABLE KEYS */;
INSERT INTO `staff_attendance_type` VALUES (1,'Present','P','yes',1,'present','label label-success',1,'2026-02-05 13:08:06','2026-02-05 13:08:06'),(2,'First Half Late','FHL','yes',1,'first_half_late','label label-warning',1,'2026-02-05 13:08:06','2026-02-05 13:08:06'),(3,'Absent','A','yes',1,'absent','label label-danger',1,'2026-02-05 13:08:06','2026-02-05 13:08:06'),(4,'Half Day','HD','yes',1,'half_day','label label-info',1,'2026-02-05 13:08:06','2026-02-05 13:08:06'),(5,'First Half Permission','FHP','yes',0,'first_half_permission',NULL,1,'2026-02-05 13:08:06','2026-02-05 13:08:06'),(6,'Second Half Late','SHL','yes',1,'second_half_late','label label-warning',1,'2026-02-05 13:08:06','2026-02-05 13:08:06'),(7,'Second Half Permission','SHP','yes',0,'second_half_permission',NULL,1,'2026-02-05 13:08:06','2026-02-05 13:08:06'),(8,'First Half Absent','FHA','yes',0,'first_half_absent',NULL,1,'2026-02-05 13:08:06','2026-02-05 13:08:06'),(9,'Second Half Absent','SHA','yes',0,'second_half_absent',NULL,1,'2026-02-05 13:08:06','2026-02-05 13:08:06');
/*!40000 ALTER TABLE `staff_attendance_type` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `staff_designation`
--

LOCK TABLES `staff_designation` WRITE;
/*!40000 ALTER TABLE `staff_designation` DISABLE KEYS */;
INSERT INTO `staff_designation` VALUES (1,'SYSTEM ADMIN',2,'yes','2025-10-24 07:24:56','2025-10-24 07:24:56'),(2,'CHIEF LIBRARIAN',2,'yes','2025-10-24 07:25:03','2025-10-24 07:25:03'),(3,'PHYSICAL EDUCATION DIRECTOR',2,'yes','2025-10-24 07:25:11','2025-10-24 07:25:11'),(4,'TRANSPORT MANAGER',2,'yes','2025-10-24 07:25:24','2025-10-24 07:25:24'),(5,'OFFICE MANAGER',2,'yes','2025-10-24 07:25:32','2025-10-24 07:25:32'),(6,'PRINCIPAL',1,'yes','2025-10-24 07:25:40','2025-10-24 07:25:40'),(7,'Sr FINANCE MANAGER',2,'yes','2025-10-24 07:26:01','2025-10-24 07:26:01'),(8,'Sr ACCOUNTANT',2,'yes','2025-10-24 07:26:19','2025-10-24 07:26:19'),(9,'ACCOUNTS EXECUTIVE',2,'yes','2025-10-24 07:26:41','2025-10-24 07:26:41'),(10,'IT INFR-MANAGER',2,'yes','2025-10-24 07:26:52','2025-10-24 07:26:52'),(11,'SENIOR SUPERINTENDENT',2,'yes','2025-10-24 07:27:02','2025-10-24 07:27:02'),(12,'ASSISTANT MANAGER',2,'yes','2025-10-24 07:27:11','2025-10-24 07:27:11'),(13,'STORE INCHARGE',2,'yes','2025-10-24 07:27:21','2025-10-24 07:27:21'),(14,'OFFICE ASSISTANT',2,'yes','2025-10-24 07:27:29','2025-10-24 07:27:29'),(15,'ATTENDER',2,'yes','2025-10-24 07:27:35','2025-10-24 07:27:35'),(16,'RECEPTIONIST',2,'yes','2025-10-24 07:27:42','2025-10-24 07:27:42'),(17,'XEROX OPERATOR',2,'yes','2025-10-24 07:27:51','2025-10-24 07:27:51'),(18,'OTHERS',3,'yes','2025-10-24 07:27:59','2025-10-24 07:27:59'),(19,'SENIOR ASSISTANT',2,'yes','2025-10-24 07:28:07','2025-10-24 07:28:07'),(20,'JUNIOR ASSISTANT',2,'yes','2025-10-24 07:28:16','2025-10-24 07:28:16'),(21,'JUNIOR ACCOUNTANT',2,'yes','2025-10-24 07:28:23','2025-10-24 07:28:23'),(22,'ELECTRICIAN',2,'yes','2025-10-24 07:28:31','2025-10-24 07:28:31'),(23,'PLUMBER',2,'yes','2025-10-24 07:28:39','2025-10-24 07:28:39'),(24,'LAB INSTRUCTOR',2,'yes','2025-10-24 07:28:46','2025-10-24 07:28:46'),(25,'NETWORK ENGINEER',2,'yes','2025-10-24 07:28:54','2025-10-24 07:28:54'),(26,'LAB ASSISTANT',2,'yes','2025-10-24 07:29:00','2025-10-24 07:29:00'),(27,'LIBRARIAN',2,'yes','2025-10-24 07:29:06','2025-10-24 07:29:06'),(28,'Asst. PHYSICAL DIRECTOR',2,'yes','2025-10-24 07:29:47','2025-10-24 07:29:47'),(29,'HOSTEL WARDEN',2,'yes','2025-10-24 07:30:02','2025-10-24 07:30:02'),(30,'DRIVER',2,'yes','2025-10-24 07:30:10','2025-10-24 07:30:10'),(31,'CASHIER',2,'yes','2025-10-24 07:30:16','2025-10-24 07:30:16'),(32,'ASSISTANT LIBRARIAN',2,'yes','2025-10-24 07:30:33','2025-10-24 07:30:33'),(33,'COURSE INSTRUCTOR',1,'yes','2025-10-24 07:30:46','2025-10-24 07:30:46'),(34,'STAFF NURSE',2,'yes','2025-10-24 07:30:55','2025-10-24 07:30:55'),(35,'EXECUTIVE DIRECTOR',2,'yes','2025-10-24 07:31:03','2025-10-24 07:31:03'),(36,'ASSOCIATE PROFESSOR',1,'yes','2025-12-26 13:56:58','2025-12-26 13:56:58'),(37,'ASSISTANT PROFESSOR',1,'yes','2025-12-26 13:56:58','2025-12-26 13:56:58'),(38,'PROFESSOR',1,'yes','2025-12-26 13:56:58','2025-12-26 13:56:58'),(39,'VICE PRINCIPAL',1,'yes','2025-12-26 13:56:59','2025-12-26 13:56:59'),(40,'ASSISTANT PROFESSOR (SG)',1,'yes','2025-12-26 13:56:59','2025-12-26 13:56:59'),(41,'HR',1,'yes','2025-12-26 13:56:59','2025-12-26 13:56:59'),(42,'PHYSICAL DIRECTOR',1,'yes','2025-12-26 13:56:59','2025-12-26 13:56:59'),(43,'TECH ASSISTANT',1,'yes','2025-12-26 13:56:59','2025-12-26 13:56:59'),(44,'CHAIRMAN',3,'yes','2025-12-26 13:56:59','2025-12-26 13:56:59'),(45,'DEAN',1,'yes','2025-12-26 13:56:59','2025-12-26 13:56:59'),(46,'HOD',1,'yes','2025-12-26 13:57:01','2025-12-26 13:57:01'),(47,'PLACEMENT',1,'yes','2025-12-26 13:57:01','2025-12-26 13:57:01'),(48,'ASSISTANT PHYSICAL DIRECTOR',2,'yes','2025-12-26 14:03:18','2025-12-26 14:03:18'),(49,'ADMISSION HEAD',2,'yes','2026-01-24 11:13:13','2026-01-24 11:13:13'),(50,'SECURITY',2,'yes','2026-02-27 19:10:26','2026-02-27 19:10:26'),(51,'HOUSE KEEPING',2,'yes','2026-02-27 19:34:34','2026-02-27 19:34:34'),(52,'ARCHAGAR',2,'yes','2026-02-27 19:35:02','2026-02-27 19:35:02'),(53,'GARDNER',2,'yes','2026-02-27 19:36:00','2026-02-27 19:36:00'),(54,'TRUSTEE',2,'yes','2026-02-27 19:37:54','2026-02-27 19:37:54'),(55,'ADMINISTRATIVE OFFICER (AO)',2,'yes','2026-03-04 14:51:18','2026-03-04 14:51:18');
/*!40000 ALTER TABLE `staff_designation` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `staff_designation_category`
--

LOCK TABLES `staff_designation_category` WRITE;
/*!40000 ALTER TABLE `staff_designation_category` DISABLE KEYS */;
INSERT INTO `staff_designation_category` VALUES (1,'Teaching','Teaching & Academic Staff','#5BC0DE','fa-book','2026-02-25 06:19:06'),(2,'Non-Teaching','Administrative & Support Staff','#28A745','fa-cogs','2026-02-25 06:19:06'),(3,'Others','Other Category Staff','#FFC107','fa-user-o','2026-02-25 06:19:06'),(4,'Maintenance','Maintenance Category Staff','#FFC107','fa-user-o','2026-02-25 06:19:06');
/*!40000 ALTER TABLE `staff_designation_category` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `staff_roles`
--

LOCK TABLES `staff_roles` WRITE;
/*!40000 ALTER TABLE `staff_roles` DISABLE KEYS */;
INSERT INTO `staff_roles` VALUES (1,7,1,0,'2025-09-25 15:40:56','2025-09-25 15:40:56');
/*!40000 ALTER TABLE `staff_roles` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `student_dashboard_settings`
--

LOCK TABLES `student_dashboard_settings` WRITE;
/*!40000 ALTER TABLE `student_dashboard_settings` DISABLE KEYS */;
INSERT INTO `student_dashboard_settings` VALUES (1,'welcome_student','',1,1,'2024-10-15 12:14:22','2025-09-25 15:40:51'),(2,'notice_board','communicate',1,1,'2024-10-15 12:14:25','2025-09-25 15:40:51'),(3,'subject_progress','lesson_plan',1,1,'2024-10-15 12:14:27','2025-09-25 15:40:51'),(4,'upcomming_class','academics',1,1,'2024-10-15 12:14:55','2025-09-25 15:40:51'),(5,'homework','homework',1,1,'2024-10-15 12:14:56','2025-09-25 15:40:51'),(6,'teacher_list','human_resource',1,1,'2024-10-15 12:14:57','2025-09-25 15:40:51'),(7,'visitor_list','front_office',1,1,'2024-10-15 12:14:58','2025-09-25 15:40:51'),(8,'library','library',1,1,'2024-10-15 12:14:59','2025-09-25 15:40:51');
/*!40000 ALTER TABLE `student_dashboard_settings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `student_edit_fields`
--

LOCK TABLES `student_edit_fields` WRITE;
/*!40000 ALTER TABLE `student_edit_fields` DISABLE KEYS */;
INSERT INTO `student_edit_fields` VALUES (1,'firstname',1,'2026-01-07 06:14:08','2026-01-07 06:14:08'),(2,'admission_date',1,'2026-01-07 06:14:13','2026-01-07 06:14:13'),(3,'lastname',1,'2026-01-07 06:14:17','2026-01-07 06:14:17'),(4,'student_photo',1,'2026-01-07 06:14:22','2026-01-07 06:14:22'),(5,'mobile_no',1,'2026-01-07 06:14:25','2026-01-07 06:14:25'),(6,'student_email',1,'2026-01-07 06:14:28','2026-01-07 06:14:28'),(7,'religion',1,'2026-01-07 06:14:32','2026-01-07 06:14:32'),(8,'cast',1,'2026-01-07 06:14:35','2026-01-07 06:14:35'),(9,'dob',1,'2026-01-07 06:14:38','2026-01-07 06:14:38'),(10,'is_blood_group',1,'2026-01-07 06:14:41','2026-01-07 06:14:41'),(11,'if_guardian_is',1,'2026-01-07 06:14:45','2026-01-07 06:14:45'),(12,'gender',1,'2026-01-07 06:14:48','2026-01-07 06:14:48'),(13,'permanent_address',1,'2026-01-07 06:14:57','2026-01-07 06:14:57'),(14,'category',0,'2026-01-07 06:15:00','2026-01-07 06:16:55'),(15,'bank_account_no',1,'2026-01-07 06:15:03','2026-01-07 06:15:03'),(16,'bank_name',1,'2026-01-07 06:15:06','2026-01-07 06:15:06'),(17,'ifsc_code',1,'2026-01-07 06:15:12','2026-01-07 06:15:12'),(18,'father_name',1,'2026-01-07 06:15:15','2026-01-07 06:15:15'),(19,'father_phone',1,'2026-01-07 06:15:17','2026-01-07 06:15:17'),(20,'father_occupation',1,'2026-01-07 06:15:20','2026-01-07 06:15:20'),(21,'mother_name',1,'2026-01-07 06:15:23','2026-01-07 06:15:23'),(22,'mother_phone',1,'2026-01-07 06:15:28','2026-01-07 06:15:28'),(23,'is_student_house',1,'2026-01-07 06:15:31','2026-01-07 06:15:31'),(24,'mother_occupation',1,'2026-01-07 06:15:33','2026-01-07 06:15:33'),(25,'guardian_name',1,'2026-01-07 06:15:36','2026-01-07 06:15:36'),(26,'guardian_relation',1,'2026-01-07 06:15:38','2026-01-07 06:15:38'),(27,'guardian_phone',1,'2026-01-07 06:15:41','2026-01-07 06:15:41'),(28,'guardian_occupation',1,'2026-01-07 06:15:44','2026-01-07 06:15:44'),(29,'guardian_address',1,'2026-01-07 06:15:46','2026-01-07 06:15:46'),(30,'guardian_email',1,'2026-01-07 06:16:03','2026-01-07 06:16:03'),(31,'national_identification_no',1,'2026-01-07 06:16:05','2026-01-07 06:16:05'),(32,'local_identification_no',1,'2026-01-07 06:16:07','2026-01-07 06:16:07'),(33,'father_pic',1,'2026-01-07 06:16:09','2026-01-07 06:16:09'),(34,'mother_pic',1,'2026-01-07 06:16:12','2026-01-07 06:16:12'),(35,'guardian_pic',1,'2026-01-07 06:16:15','2026-01-07 06:16:15'),(36,'student_height',1,'2026-01-07 06:16:18','2026-01-07 06:16:18'),(37,'student_weight',1,'2026-01-07 06:16:21','2026-01-07 06:16:21'),(38,'previous_school_details',1,'2026-01-07 06:16:23','2026-01-07 06:16:23');
/*!40000 ALTER TABLE `student_edit_fields` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `staff` (superadmin only)
--

LOCK TABLES `staff` WRITE;
/*!40000 ALTER TABLE `staff` DISABLE KEYS */;
INSERT INTO `staff` VALUES (1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'ADMIN001','ADMIN001',0,0,NULL,NULL,'','','Super Admin','','','','',NULL,'','admin@minerva.com','2020-01-01','',NULL,NULL,'','',NULL,'','','$2y$12$PIOkaxhP/AomzsyzqdiAAe1Pj2vtVLMIM7znyHwp7Fkkc94NAqeaK','Male','','','','','','',NULL,'','','','','','','','','','','','',0,1,'',NULL,NULL,NULL,'2020-01-01 00:00:00','2020-01-01 00:00:00',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL);
/*!40000 ALTER TABLE `staff` ENABLE KEYS */;
UNLOCK TABLES;

SET FOREIGN_KEY_CHECKS=1;

/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-04-12 23:17:37

-- -------------------------------------------------------
-- Vehicle expiry notification tables (added 2026-05-xx)
-- -------------------------------------------------------
DROP TABLE IF EXISTS `vehicle_expiry_assignees`;
CREATE TABLE `vehicle_expiry_assignees` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `staff_id` int(11) NOT NULL,
  `slot` tinyint(1) NOT NULL DEFAULT 1 COMMENT '1, 2 or 3',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_slot` (`slot`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `vehicle_notification_config`;
CREATE TABLE `vehicle_notification_config` (
  `id` int(1) NOT NULL DEFAULT 1,
  `wa_template_id` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
INSERT INTO `vehicle_notification_config` (id, wa_template_id) VALUES (1, NULL);
