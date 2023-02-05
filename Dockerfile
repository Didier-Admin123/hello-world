FROM tomcat:8-jre8

MAINTAINER Didier Dorcelus
COPY ./webapp/target/webapp.war /usr/local/tomcat/webapps
