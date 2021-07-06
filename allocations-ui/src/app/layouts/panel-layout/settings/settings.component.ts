/**
 * Copyrights Allocations 2021. All rights reserved
 *
 * The code, text and other elements of this application/file is copyrighted
 * You may not remove any copyright or other proprietary notices contained in this file
 * The rights granted to you use this application in your organization for your
 * business/personal purpose and not to sell or modify
 *
 * Developed by: Mohamed Asif
 * Email: mohamedasif18@gmail.com
 */
import { Component, OnInit, OnDestroy } from '@angular/core';
import { FormBuilder, FormGroup, Validators, FormControl } from '@angular/forms';
import { noop, Subscription } from 'rxjs';
import { finalize } from 'rxjs/operators';
import { SnackbarService, SettingsService, AuthenticationService } from '../../../_services';
import { Title } from '@angular/platform-browser';
import { DefaultApiResponse } from '../../../_models';
import * as _ from 'lodash';

declare interface SettingsData {
  name: string;
  businessName: string;
  email: string;
  phone: string;
  settings: any;
  username: string;
  instances: object[];
}

@Component({
  selector: 'app-settings',
  templateUrl: './settings.component.html',
  styleUrls: ['./settings.component.scss']
})
export class SettingsComponent implements OnInit, OnDestroy {

  public isFormLoading: boolean = false;
  public form: FormGroup;
  private submitted = false;
  private subscriptions: Subscription[] = [];
  public settingsData: SettingsData = {
    name: null,
    businessName: null,
    email: null,
    phone: null,
    settings: null,
    username: null,
    instances: null
  };

  constructor(
    private formBuilder: FormBuilder,
    private settingsService: SettingsService,
    private snackBar: SnackbarService,
    private titleService: Title,
    private authenticationService: AuthenticationService) {}

  ngOnInit(): void {
    this.titleService.setTitle('Settings');
    this.initForm();
    this.getSettings();
  }

  private getSettings(): void {
    this.settingsService.getSettings()
      .pipe(finalize(() => this.isFormLoading = false))
      .subscribe((response: DefaultApiResponse) => {
        this.settingsData = response.data;
        this.form.patchValue({
          instance: _.isObject(this.settingsData.settings) ? this.settingsData.settings.activeInstanceId : null,
          name: this.settingsData.name,
          businessName: this.settingsData.businessName,
          email: this.settingsData.email,
          phone: this.settingsData.phone,
          username: this.settingsData.username,
          password: null
        });
      });
  }

  private initForm(): void {
    this.form = this.formBuilder.group({
      instance: new FormControl(null, [
        Validators.required
      ]),
      name: new FormControl(null, {
        validators: [
          Validators.required,
          Validators.maxLength(35),
          Validators.minLength(3),
          Validators.pattern(/^[ a-zA-Z0-9]+$/)
        ]
      }),
      businessName: new FormControl(null, {
        validators: [
          Validators.required,
          Validators.maxLength(35),
          Validators.minLength(3)
        ]
      }),
      email: new FormControl(null, {
        validators: [
          Validators.required,
          Validators.email
        ],
      }),
      phone: new FormControl(null, {
        validators: [
          Validators.required,
          Validators.maxLength(10),
          Validators.minLength(10)
        ]
      }),
      username: new FormControl({value: null, disabled: true}),
      password: new FormControl(null, {
        validators: [
          Validators.maxLength(15),
          Validators.minLength(5),
          Validators.pattern(/^\S*$/)
        ]
      })
    });
  }

  get f() {
    return this.form.controls;
  }

  onSubmit(): void {
    this.submitted = true;

    if (this.form.invalid) {
      return;
    }

    this.isFormLoading = true;
    this.form.value.password = (_.size(this.form.value.password) >= 5) ? btoa(this.form.value.password) : null;
    this.settingsService.saveSettings(this.form.value)
      .pipe(finalize(() => this.isFormLoading = false))
      .subscribe((response: DefaultApiResponse) => {
        if (response) {
          this.authenticationService.getUserInfo();
          this.snackBar.show(response.message, response.status ? 'success' : 'danger');
        }
      }, () => noop());
  }

  ngOnDestroy() {
    this.subscriptions.forEach(subscription => subscription.unsubscribe());
  }
}
