CREATE TABLE `online_admission_ug_details` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `online_admission_id` int(11) NOT NULL,
  `ug_course_id` varchar(255) DEFAULT NULL,
  `school_name_x` varchar(255) DEFAULT NULL,
  `passing_year_x` varchar(255) DEFAULT NULL,
  `maths_marks` varchar(255) DEFAULT NULL,
  `total_maths` varchar(255) DEFAULT NULL,
  `physics_marks` varchar(255) DEFAULT NULL,
  `total_physics` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `online_admission_pg_details` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `online_admission_id` int(11) NOT NULL,
  `pg_course_id` varchar(255) DEFAULT NULL,
  `qualifying_exam` varchar(255) DEFAULT NULL,
  `branch` varchar(255) DEFAULT NULL,
  `year_of_passing` varchar(255) DEFAULT NULL,
  `college_name` varchar(255) DEFAULT NULL,
  `university_name` varchar(255) DEFAULT NULL,
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
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `online_admission_lateral_details` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `online_admission_id` int(11) NOT NULL,
  `lateral_course_id` varchar(255) DEFAULT NULL,
  `school_name_x` varchar(255) DEFAULT NULL,
  `passing_year_x` varchar(255) DEFAULT NULL,
  `pre_final_sem_subjects` text,
  `final_sem_subjects` text,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `online_admission_references` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `online_admission_id` int(11) NOT NULL,
  `referrer_name` varchar(255) DEFAULT NULL,
  `relationship` varchar(255) DEFAULT NULL,
  `phone_no` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `online_admission_nata_details` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `online_admission_id` int(11) NOT NULL,
  `nata_score` varchar(255) DEFAULT NULL,
  `application_number` varchar(255) DEFAULT NULL,
  `nata_year` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
