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
        CONTAINER_NAME = "my-app"
    }

    stages {
        stage('Clone Repository on Jenkins') {
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

        stage('Copy App to Remote Server') {
            steps {
                sshagent(['git_cred_ssh']) {
                    sh """
                        scp -o StrictHostKeyChecking=no -r ${WORKSPACE}/hello-world ${REMOTE_USER}@${REMOTE_HOST}:${APP_DIR}
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
                        docker build -t ${IMAGE_NAME}:${IMAGE_TAG} .
                        
                        exit
                        EOF
                    """
                }
            }
        }

        stage('Push Docker Image to DockerHub') {
            steps {
                sshagent(['git_cred_ssh']) {
                    withCredentials([usernamePassword(credentialsId: 'docker_cred', usernameVariable: 'DOCKER_USER', passwordVariable: 'DOCKER_PASS')]) {
                        sh """
                            ssh -o StrictHostKeyChecking=no ${REMOTE_USER}@${REMOTE_HOST} << 'EOF'
                            echo "Logging into DockerHub and pushing image..."

                            # Login to DockerHub using Jenkins credentials
                            echo "$DOCKER_PASS" | podman login --username "$DOCKER_USER" --password-stdin docker.io

                            # Verify login success
                            podman login --get-login docker.io

                            # Push the image
                            podman push ${IMAGE_NAME}:${IMAGE_TAG} docker.io/${IMAGE_NAME}:${IMAGE_TAG}
                            
                            exit
                            EOF
                        """
                    }
                }
            }
        }

        stage('Stop & Remove Existing Container (If Running)') {
            steps {
                sshagent(['git_cred_ssh']) {
                    sh """
                        ssh -o StrictHostKeyChecking=no ${REMOTE_USER}@${REMOTE_HOST} << 'EOF'
                        echo "Checking if the container '${CONTAINER_NAME}' is running..."

                        # Check if the container is running and stop it
                        if podman ps --format "{{.Names}}" | grep -q "^${CONTAINER_NAME}$"; then
                            echo "Stopping running container..."
                            podman stop ${CONTAINER_NAME}
                        fi

                        # Check if the container exists and remove it
                        if podman ps -a --format "{{.Names}}" | grep -q "^${CONTAINER_NAME}$"; then
                            echo "Removing existing container..."
                            podman rm ${CONTAINER_NAME}
                        fi

                        exit
                        EOF
                    """
                }
            }
        }

        stage('Run New Application Container') {
            steps {
                sshagent(['git_cred_ssh']) {
                    sh """
                        ssh -o StrictHostKeyChecking=no ${REMOTE_USER}@${REMOTE_HOST} << 'EOF'
                        echo "Starting application with new image..."
                        
                        # Pull the latest image from Docker Hub
                        podman pull docker.io/${IMAGE_NAME}:${IMAGE_TAG}

                        # Run the container with the new image
                        podman run -d --name ${CONTAINER_NAME} -p 3000:3000 docker.io/${IMAGE_NAME}:${IMAGE_TAG}
                        
                        exit
                        EOF
                    """
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
