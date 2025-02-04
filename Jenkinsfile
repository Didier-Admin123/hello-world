pipeline {
    agent any

    environment {
        REMOTE_HOST = "172.23.215.51"
        REMOTE_USER = "jenkins"
        APP_DIR = "/opt"
        GIT_REPO = "git@github.com:Didier-Admin123/hello-world.git"
        GIT_BRANCH = "ci-cd-pipeline"
        IMAGE_NAME = "didierdorcelus1/nodejs"
        IMAGE_TAG = "${BUILD_NUMBER}"
        DOCKER_CREDENTIALS = "docker_cred"
        SONAR_HOST_URL = "http://192.168.0.11:9000"
        SONAR_PROJECT_KEY = "hello-world"
    }

    stages {
        stage('Clone Repository') {
            steps {
                sshagent(['git_cred_ssh']) {
                    sh """
                        export GIT_SSH_COMMAND='ssh -o StrictHostKeyChecking=no'
                        rm -rf hello-world || true
                        git clone -b ${GIT_BRANCH} --single-branch ${GIT_REPO} hello-world
                    """
                }
            }
        }

        stage('Build Docker Image on Remote Server') {
            steps {
                sshagent(['git_cred_ssh']) {
                    sh """
                        ssh -o StrictHostKeyChecking=no ${REMOTE_USER}@${REMOTE_HOST} << 'EOF'
                        echo "Building Docker image on remote server..."
                        
                        # Navigate to the copied project directory
                        cd ${APP_DIR}/hello-world
                        
                        # Build the Docker image
                        podman build -t ${IMAGE_NAME}:${IMAGE_TAG} .
                        
                        exit
                        EOF
                    """
                }
            }
        }
        
        stage('Run SonarQube Scan on Remote Server') {
            steps {
                withCredentials([string(credentialsId: 'sonarqube', variable: 'SONAR_TOKEN')]) {
                    sshagent(['git_cred_ssh']) {
                        sh """
                            ssh -o StrictHostKeyChecking=no ${REMOTE_USER}@${REMOTE_HOST} << 'EOF'
                            echo "Running SonarQube scan inside podman container..."
                            
                            # Pull the full SonarQube scanner image (avoid short-name resolution)
                            podman pull docker.io/sonarsource/sonar-scanner-cli

                            # Run the SonarQube scanner inside a podman container
                            podman run --rm --quiet --name sonar-scan \\
                                -v ${APP_DIR}/hello-world:/usr/src \\
                                docker.io/sonarsource/sonar-scanner-cli \\
                                -Dsonar.projectKey=${SONAR_PROJECT_KEY} \\
                                -Dsonar.sources=/usr/src \\
                                -Dsonar.host.url=${SONAR_HOST_URL} \\
                                -Dsonar.login="${SONAR_TOKEN}"

                            exit
                            EOF
                        """
                    }
                }
            }
        }

        
        stage('Quality Gate Check') {
            steps {
                timeout(time: 2, unit: 'MINUTES') {
                    script {
                        def qg = waitForQualityGate()
                        if (qg.status != 'OK') {
                            error "Quality Gate failed: ${qg.status}"
                        }
                    }
                }
            }
        }
    }

    post {
        success {
            echo "✅ Deployment successful!"
        }
        failure {
            echo "❌ Deployment failed. Check logs."
        }
    }
}
