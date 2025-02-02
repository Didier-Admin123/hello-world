pipeline {
    agent any

    environment {
        REMOTE_HOST = "172.23.215.51"
        REMOTE_USER = "jenkins"
        APP_DIR = "/opt"
        GIT_REPO = "git@github.com:Didier-Admin123/hello-world.git"
        GIT_BRANCH = "ci-cd-pipeline"
        DOCKER_IMAGE_NAME = "didierdorcelus1/nodejs"
        DOCKER_CREDENTIALS = "docker_cred"  
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
                script {
                    def imageTag = "${BUILD_NUMBER}"  // Use Jenkins build number as the Docker tag
                    sshagent(['git_cred_ssh']) {
                        sh """
                            ssh -o StrictHostKeyChecking=no ${REMOTE_USER}@${REMOTE_HOST} << 'EOF'
                            echo "Building Docker image on remote server..."
                            
                            # Navigate to the copied project directory
                            cd ${APP_DIR}/hello-world
                            
                            # Build the Docker image
                            docker build -t ${DOCKER_IMAGE_NAME}:${imageTag} .
                            # Exit the SSH session to allow Jenkins to finish
                            disown
                            exit
                            EOF
                        """
                    }
                }
            }
        }

        stage('Push Docker Image to DockerHub') {
            steps {
                script {
                    def imageTag = "${BUILD_NUMBER}"
                    withCredentials([usernamePassword(credentialsId: "${DOCKER_CREDENTIALS}", usernameVariable: 'DOCKER_USERNAME', passwordVariable: 'DOCKER_PASSWORD')]) {
                        sh """
                            ssh -o StrictHostKeyChecking=no ${REMOTE_USER}@${REMOTE_HOST} << 'EOF'
                            echo "Pushing Docker image to DockerHub..."
                            
                            # Docker login and push the image
                            echo \$DOCKER_PASSWORD | docker login -u \$DOCKER_USERNAME --password-stdin
                            docker push ${DOCKER_IMAGE_NAME}:${imageTag}
                            EOF
                        """
                    }
                }
            }
        }

        stage('Run Commands on Remote Server') {
            steps {
                sshagent(['git_cred_ssh']) {
                    sh """
                        ssh -o StrictHostKeyChecking=no ${REMOTE_USER}@${REMOTE_HOST} << 'EOF'
                        echo "Connected to remote build server!"

                        # Install Node.js and npm (if not installed)
                        sudo dnf install -y nodejs npm

                        # Navigate to the copied project directory
                        cd ${APP_DIR}/hello-world/app

                        # Install dependencies
                        npm install

                        # Run tests
                        npm test

                        # Check if the app is already running and kill the process if necessary
                        app_pid=\$(pgrep -f 'node app.js')
                        if [ -n "\$app_pid" ]; then
                            echo "Stopping the existing app with PID: \$app_pid"
                            kill -9 \$app_pid || true
                        fi

                        # Start the app in the background with logging
                        nohup npm start > app.log 2>&1 &

                        # Exit the SSH session to allow Jenkins to finish
                        disown
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
