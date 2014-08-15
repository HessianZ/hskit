-- 角色
INSERT INTO `roles` VALUES 
(1,'Administrator','ALL','NONE',NULL,1407995959,1407995959),
(2,'Manager','ALL','manager/*',NULL,1407995959,1407995959);


-- 菜单
INSERT INTO `menus` VALUES 
(1,0,0,'首页','home','/admin/home',NULL,1000),
(2,0,0,'系统设置','cog','',NULL,1000),
(3,0,2,'用户管理','user','/admin/manager',NULL,1000),
(4,0,2,'角色管理','th-list','/admin/role',NULL,1000);
