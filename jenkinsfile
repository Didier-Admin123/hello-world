def img
def dockerhost = '172.31.20.62'
def artifactoryurl = 'http://172.31.26.184:8082/artifactory/'
pipeline {
    agent any
    environment{
        PATH = "/opt/apache-maven-3.5.3/bin:$PATH"
	//To push an image to Docker Hub, you must first name your local image using your Docker Hub username and the repository name that you created through Docker Hub on the web.
        registry = "didierdorcelus1/testaudipage" 
        registryCredential = 'dockerhub'
        dockerImage = ''
        CI = true
        ARTIFACTORY_ACCESS_TOKEN = credentials('Artifactory-token')
    }
    stages {
        stage('Clone gitHub Code') {
            steps {
                // Get some code from a GitHub repository
                git url: 'https://github.com/Didier-Admin123/hello-world.git', branch: 'main'
            }
        }
    stage('Build Maven Code Create .war') {
            steps {
                // Get some code from a GitHub repository
                sh "mvn clean install"
            }
        }
    stage('Upload to Artifactory') {
      agent {
        docker {
          image 'releases-docker.jfrog.io/jfrog/jfrog-cli-v2:2.2.0' 
          reuseNode true
        }
      }
      steps {
        sh 'jfrog rt upload --url ${artifactoryurl} --access-token ${ARTIFACTORY_ACCESS_TOKEN} webapp/target/webapp.war didiertest/'
      }
    }
  
    stage('Build Docker Image') {
            steps {
                script {
                    img = registry + ":${env.BUILD_ID}"
                    println ("${img}")
                    dockerImage = docker.build("${img}")
                }
            }
    }
    stage('Push Image to DockerHub') {
        steps {
            script {
                docker.withRegistry( 'https://registry.hub.docker.com ', registryCredential ) {
                    dockerImage.push()
                }
            }
        }
    }

    stage('PULL/RUN Image from Docker Server') {
        steps {
            script {
                def stopcontainer = "docker stop ${JOB_NAME}"
                def delcontName = "docker rm ${JOB_NAME}"
                def delimages = 'docker image prune -a --force'
                def drun = "docker run -d --name ${JOB_NAME} -p 8090:8080 ${img}"
                println "${drun}"
                sshagent(['docker']) {
                    sh returnStatus: true, script: "ssh -o StrictHostKeyChecking=no docker@${dockerhost} ${stopcontainer} "
                    sh returnStatus: true, script: "ssh -o StrictHostKeyChecking=no docker@${dockerhost} ${delcontName}"
                    sh returnStatus: true, script: "ssh -o StrictHostKeyChecking=no docker@${dockerhost} ${delimages}"

                // some block
                    sh "ssh -o StrictHostKeyChecking=no docker@${dockerhost} ${drun}"
                }
            }
        }
    }

}}
