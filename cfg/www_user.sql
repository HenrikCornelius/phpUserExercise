use sardb;

drop table www_grant;
drop table www_permission;
drop table www_user;
create table www_user
( id                    int not null
, username              varchar(128) not null
, first_name            varchar(128) not null default ''
, last_name             varchar(128) not null default ''
, email                 varchar(255) not null default ''
, is_enabled            boolean default true
, is_superuser          boolean default false
, password              varchar(128) not null
, password_expiry       date not null default '9999-12-31'
, created_by            int default 1 
, created_dtm           datetime not null default current_timestamp
, updated_by            int default null 
, updated_dtm           datetime default null on update current_timestamp
, deleted_by            int default null 
, deleted_dtm           datetime default '9999-12-31 23:59:59'
, constraint www_user_pk primary key (id)
, constraint www_user_uk1 unique (username, deleted_dtm)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;

insert into www_user (id, username, password, created_by, is_superuser) values( 1, 'admin', 'admin', 1, true);
insert into www_user (id, username, password, created_by, is_enabled) values( 2, 'anonymous', '', 1, true);
insert into www_user (id, username, password, created_by, is_enabled) values( 3, 'demo', 'demo', 1, true);

alter table www_user auto_increment = 3;
alter table www_user modify column id int not null auto_increment;
alter table www_user add constraint www_user_fk1 foreign key (created_by) references www_user (id);
alter table www_user add constraint www_user_fk2 foreign key (updated_by) references www_user (id);

create table www_permission
( id                    int not null auto_increment
, name                  varchar(128) not null
, description           varchar(128) not null 
, notes                 varchar(1000) not null default ''
, created_by            int default 1
, created_dtm           datetime not null default current_timestamp
, updated_by            int default null
, updated_dtm           datetime default null on update current_timestamp
, deleted_by            int default null 
, deleted_dtm           datetime default '9999-12-31 23:59:59'
, constraint www_permission_pk primary key (id)
, constraint www_permission_uk1 unique (name, deleted_dtm)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;
--
insert into www_permission (name,description) values('User_adm', 'User administration');
insert into www_permission (name,description) values('Config_Counter', 'Configure counters');
insert into www_permission (name,description) values('Display_Counters', 'Display counters');
--
--
--
create table www_grant
( fk_user_id            int not null
, fk_permission_id      int not null
, can_update            boolean default false
, created_by            int default 1
, created_dtm           datetime not null default current_timestamp
, updated_by            int default null
, updated_dtm           datetime default null on update current_timestamp
, deleted_by            int default null 
, deleted_dtm           datetime default '9999-12-31 23:59:59'
, constraint www_grant_pk primary key (fk_user_id,fk_permission_id)
, constraint www_grant_fk1 foreign key (fk_user_id) references www_user (id)
, constraint www_grant_fk2 foreign key (fk_permission_id) references www_permission (id)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;
--
--
--
create table www_sessions
( session_id            varchar(255) not null
, session_data          mediumtext not null
, session_path          varchar(255)
, session_name          varchar(255)
, created_dtm           datetime not null default current_timestamp
, updated_dtm           datetime not null default current_timestamp
, constraint www_sessions_pk primary key (session_id)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;
