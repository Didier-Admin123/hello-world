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
        SONARQUBE_SERVER = "SonarQube"   // SonarQube Server Name in Jenkins
        SONARQUBE_TOKEN = credentials('sonarqube_token')
        NEXUS_REPO = "nexus-repo"
    }

    stages {
        // Cloning the latest code from Git repository
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

        // Install all necessary dependencies for the Node.js application
        stage('Install Dependencies') {
            steps {
                dir('hello-world') {
                    sh "npm install"
                }
            }
        }

        // Code linting ensures the code follows best practices and coding standards (Prevents bad formatting)
        stage('Lint Code') {
            steps {
                dir('hello-world') {
                    sh "npx eslint . || true"  // ESLint checks for syntax issues
                }
            }
        }

        // SonarQube analyzes the code for vulnerabilities, security risks, and bad coding practices
        stage('SonarQube Code Analysis') {
            steps {
                withSonarQubeEnv('SonarQube') {
                    dir('hello-world') {
                        sh """
                            sonar-scanner \
                            -Dsonar.projectKey=hello-world \
                            -Dsonar.sources=. \
                            -Dsonar.host.url=http://sonarqube.local:9000 \
                            -Dsonar.login=${SONARQUBE_TOKEN}
                        """
                    }
                }
            }
        }

        // If SonarQube detects major issues, this step will stop the pipeline from continuing
        stage('Check SonarQube Quality Gate') {
            steps {
                timeout(time: 5, unit: 'MINUTES') {
                    waitForQualityGate abortPipeline: true
                }
            }
        }

        // Snyk scans dependencies for known security vulnerabilities (e.g., outdated or vulnerable packages)
        stage('Security Scan with Snyk') {
            steps {
                dir('hello-world') {
                    sh "npx snyk test || true"
                }
            }
        }

        // Runs unit and integration tests to ensure the application is functioning correctly
        stage('Run Unit & Integration Tests') {
            steps {
                dir('hello-world') {
                    sh "npm test"
                }
            }
        }

        // Builds the application artifact (e.g., minifies and packages the code)
        stage('Build Application Artifact') {
            steps {
                dir('hello-world') {
                    sh "npm run build"
                }
            }
        }

        // Uploads the built application artifact (e.g., ZIP file) to Nexus for storage and versioning
        stage('Publish to Nexus') {
            steps {
                withCredentials([usernamePassword(credentialsId: 'nexus_cred', usernameVariable: 'NEXUS_USER', passwordVariable: 'NEXUS_PASS')]) {
                    dir('hello-world') {
                        sh """
                            curl -u $NEXUS_USER:$NEXUS_PASS --upload-file build.zip \
                            http://nexus.local:8081/repository/${NEXUS_REPO}/hello-world/build-${BUILD_NUMBER}.zip
                        """
                    }
                }
            }
        }

        // Builds a Docker container image and scans it with Trivy for security issues
        stage('Build & Scan Docker Image') {
            steps {
                sshagent(['git_cred_ssh']) {
                    sh """
                        ssh -o StrictHostKeyChecking=no ${REMOTE_USER}@${REMOTE_HOST} << 'EOF'
                        cd ${APP_DIR}/hello-world
                        podman build -t ${IMAGE_NAME}:${IMAGE_TAG} .
                        podman save -o ${IMAGE_NAME}.tar ${IMAGE_NAME}:${IMAGE_TAG}
                        trivy image --exit-code 1 --severity HIGH,CRITICAL ${IMAGE_NAME}.tar || exit 1
                        exit
                        EOF
                    """
                }
            }
        }

        // Pushes the Docker image to a container registry (e.g., DockerHub) for deployment
        stage('Sign & Push Docker Image') {
            steps {
                sshagent(['git_cred_ssh']) {
                    withCredentials([usernamePassword(credentialsId: 'docker_cred', usernameVariable: 'DOCKER_USER', passwordVariable: 'DOCKER_PASS')]) {
                        sh """
                            ssh -o StrictHostKeyChecking=no ${REMOTE_USER}@${REMOTE_HOST} << 'EOF'
                            echo "$DOCKER_PASS" | podman login --username "$DOCKER_USER" --password-stdin docker.io
                            podman push ${IMAGE_NAME}:${IMAGE_TAG} docker.io/${IMAGE_NAME}:${IMAGE_TAG}
                            exit
                            EOF
                        """
                    }
                }
            }
        }

        // Deploys the application in a containerized environment (e.g., OpenShift, Kubernetes, or a standalone server)
        stage('Deploy to OpenShift') {
            steps {
                sshagent(['git_cred_ssh']) {
                    sh """
                        ssh -o StrictHostKeyChecking=no ${REMOTE_USER}@${REMOTE_HOST} << 'EOF'
                        podman pull ${IMAGE_NAME}:${IMAGE_TAG}
                        podman stop my-app || true
                        podman rm my-app || true
                        podman run -d --name my-app -p 3000:3000 ${IMAGE_NAME}:${IMAGE_TAG}
                        exit
                        EOF
                    """
                }
            }
        }

        // Checks if the deployed application is running correctly by making an HTTP request
        stage('Post-Deployment Health Check') {
            steps {
                script {
                    def response = sh(script: "curl -s -o /dev/null -w '%{http_code}' http://$REMOTE_HOST:3000", returnStdout: true).trim()
                    if (response != "200") {
                        error("🚨 Deployment failed! Health check returned ${response}")
                    } else {
                        echo "✅ Application is healthy!"
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