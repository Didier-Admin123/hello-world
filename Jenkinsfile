### Jenkinsfile (CI/CD Pipeline Definition)

pipeline {
    agent any
    environment {
        REGISTRY = "docker.io"
        IMAGE_NAME = "myapp"
    }
    stages {
        stage('Checkout Code') {
            steps {
                git 'https://github.com/example/repo.git'
            }
        }
        stage('Build & Test') {
            steps {
                sh 'npm install'
                sh 'npm test'
            }
        }
        stage('Build Docker Image') {
            steps {
                sh 'docker build -t $REGISTRY/$IMAGE_NAME:latest .'
            }
        }
        stage('Push to Nexus/Artifactory') {
            steps {
                withCredentials([usernamePassword(credentialsId: 'nexus-creds', usernameVariable: 'NEXUS_USER', passwordVariable: 'NEXUS_PASS')]) {
                    sh 'docker login -u $NEXUS_USER -p $NEXUS_PASS $REGISTRY'
                    sh 'docker push $REGISTRY/$IMAGE_NAME:latest'
                }
            }
        }
        stage('Deploy to OpenShift') {
            steps {
                sh 'helm upgrade --install myapp ./helm --set image=$REGISTRY/$IMAGE_NAME:latest'
            }
        }
    }
}
