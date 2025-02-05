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
   
        
        stage('Run ESLint on JavaScript Code') {
            steps {
                sshagent(['git_cred_ssh']) {
                    sh """
                        ssh -o StrictHostKeyChecking=no ${REMOTE_USER}@${REMOTE_HOST} << 'EOF'
                        echo "Running ESLint inside podman container..."
        
                        # Pull Node.js image if not already available
                        podman pull docker.io/library/node:latest
        
                        # Run ESLint inside the container with proper permissions
                        podman run --rm --user 0 --name eslint-check \\
                            -v ${APP_DIR}/hello-world:/usr/src/app \\
                            -w /usr/src/app \\
                            node:latest sh -c "
                                npm install --unsafe-perm eslint && npx eslint ."
                        
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
