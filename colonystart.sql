CREATE DATABASE colony;
CREATE USER 'colonyuser'@'localhost' IDENTIFIED BY 'StrogonovPasswaaaHeend123!';
GRANT ALL PRIVILEGES ON colony.* TO 'colonyuser'@'localhost';
FLUSH PRIVILEGES;
EXIT;
